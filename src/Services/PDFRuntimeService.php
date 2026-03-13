<?php
namespace Services;

class PDFRuntimeService
{

    private string $tmpPdfDir;
    private string $tmpPagesDir;

    public function __construct()
    {
        $this->tmpPdfDir = TMP_BASE . '/pdf';
        $this->tmpPagesDir = TMP_BASE . '/pages';
        putenv('PATH=' . getenv('PATH') . ':/opt/homebrew/bin:/usr/local/bin');
    }

    // ── Validate uploaded PDF ─────────────────────────────────
    public function validate(array $file, string $plan): array
    {
        $limits = PDF_LIMITS[$plan] ?? PDF_LIMITS['free'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'File upload failed. Please try again.'];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if ($mime !== 'application/pdf') {
            return ['success' => false, 'message' => 'Only PDF files are allowed.'];
        }

        $maxBytes = $limits['size_mb'] * 1024 * 1024;
        if ($file['size'] > $maxBytes) {
            return ['success' => false, 'message' => "PDF size exceeds your plan limit of {$limits['size_mb']}MB."];
        }

        return ['success' => true];
    }

    // ── Store PDF temporarily ─────────────────────────────────
    public function store(array $file): string
    {
        $sessionId = session_id();

        if (!is_dir($this->tmpPdfDir) && !@mkdir($this->tmpPdfDir, 0775, true) && !is_dir($this->tmpPdfDir)) {
            throw new \RuntimeException('Could not create temporary PDF directory.');
        }

        $destPath = $this->tmpPdfDir . "/pdf_{$sessionId}.pdf";
        if (!@move_uploaded_file($file['tmp_name'], $destPath)) {
            throw new \RuntimeException('Failed to store uploaded PDF.');
        }
        return $destPath;
    }

    // ── Get page count ────────────────────────────────────────
    public function getPageCount(string $pdfPath): int
    {
        $output = shell_exec("pdfinfo " . escapeshellarg($pdfPath) . " 2>/dev/null");
        if ($output && preg_match('/Pages:\s+(\d+)/', $output, $m)) {
            return (int) $m[1];
        }

        $content = @file_get_contents($pdfPath);
        if ($content === false) {
            return 0;
        }
        preg_match_all('/\/Count\s+(\d+)/', $content, $matches);
        if (!empty($matches[1])) {
            return (int) max($matches[1]);
        }

        return 0;
    }

    // ── Get Ghostscript path ──────────────────────────────────
    public function getGsPath(): string
    {
        if (file_exists('/opt/homebrew/bin/gs'))
            return '/opt/homebrew/bin/gs';
        if (file_exists('/usr/local/bin/gs'))
            return '/usr/local/bin/gs';
        return 'gs';
    }

    // ── Convert PDF pages to images ───────────────────────────
    public function convertToImages(string $pdfPath, int $maxPages): array
    {
        $sessionId = session_id();
        $outDir = $this->tmpPagesDir . "/{$sessionId}";

        if (!is_dir($outDir) && !@mkdir($outDir, 0775, true) && !is_dir($outDir)) {
            throw new \RuntimeException('Could not create temporary image directory.');
        }

        $gs = $this->getGsPath();
        $images = [];

        for ($page = 1; $page <= $maxPages; $page++) {
            $outFile = $outDir . "/page_{$page}.png";

            $cmd = sprintf(
                '%s -dNOPAUSE -dBATCH -sDEVICE=png16m -r150 -dFirstPage=%d -dLastPage=%d -sOutputFile=%s %s 2>/dev/null',
                $gs,
                $page,
                $page,
                escapeshellarg($outFile),
                escapeshellarg($pdfPath)
            );

            shell_exec($cmd);

            if (file_exists($outFile)) {
                $images[] = $outFile;
            }
        }

        return $images;
    }

    // ── Delete all temp files for session ─────────────────────
    public function cleanup(): void
    {
        $sessionId = session_id();

        $pdf = $this->tmpPdfDir . "/pdf_{$sessionId}.pdf";
        if (file_exists($pdf))
            @unlink($pdf);

        $pagesDir = $this->tmpPagesDir . "/{$sessionId}";
        if (is_dir($pagesDir)) {
            array_map(static fn($path) => @unlink($path), glob($pagesDir . '/*') ?: []);
            @rmdir($pagesDir);
        }
    }
}
