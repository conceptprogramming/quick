<?php

namespace Services;

class AIService
{
    private string $apiKey;
    private string $model;
    private string $apiBase;

    public function __construct()
    {
        $this->apiKey  = $_ENV['OPENAI_API_KEY'] ?? '';
        $this->model   = 'gpt-4o';
        $this->apiBase = 'https://api.openai.com/v1/chat/completions';
    }


    // ────────────────────────────────────────────────────────────
    //  Core API Call
    // ────────────────────────────────────────────────────────────

    private function call(array $messages, int $maxTokens = 2000): ?string
    {
        $payload = [
            'model'       => $this->model,
            'messages'    => $messages,
            'max_tokens'  => $maxTokens,
            'temperature' => 0.7,
        ];

        $ch = curl_init($this->apiBase);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 60,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            return null;
        }

        $data = json_decode($response, true);
        return $data['choices'][0]['message']['content'] ?? null;
    }


    // ────────────────────────────────────────────────────────────
    //  Markdown → HTML Converter
    // ────────────────────────────────────────────────────────────

    private function markdownToHtml(string $text): string
    {
        // Bold **text** or __text__
        $text = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text);
        $text = preg_replace('/__(.+?)__/s',      '<strong>$1</strong>', $text);

        // Italic *text* or _text_
        $text = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $text);
        $text = preg_replace('/_(.+?)_/s',   '<em>$1</em>', $text);

        // Inline code `code`
        $text = preg_replace('/`(.+?)`/', '<code>$1</code>', $text);

        // Numbered list  1. item
        $text = preg_replace('/^\d+\.\s+(.+)$/m', '<li>$1</li>', $text);
        $text = preg_replace('/(<li>.*<\/li>)/s',  '<ol>$1</ol>', $text);

        // Bullet list  - item or * item
        $text = preg_replace('/^[-\*]\s+(.+)$/m', '<li>$1</li>', $text);

        // Line breaks
        $text = nl2br($text);

        return $text;
    }


    // ────────────────────────────────────────────────────────────
    //  Chat with PDF
    // ────────────────────────────────────────────────────────────

    public function chat(string $fullText, string $userQuestion): ?string
    {
        $userQuestion = trim($userQuestion);

        if ($userQuestion === '') {
            return null;
        }

        if (mb_strlen($userQuestion) > 200) {
            return null;
        }

        $messages = [
            [
                'role'    => 'system',
                'content' => 'You are a helpful assistant. Answer questions based ONLY on the provided document text. If the answer is not in the document, say so clearly.',
            ],
            [
                'role'    => 'user',
                'content' => "Document Text:\n\n{$fullText}\n\n---\n\nQuestion: {$userQuestion}",
            ],
        ];

        $result = $this->call($messages, 1000);
        return $result ? $this->markdownToHtml($result) : null;
    }


    // ────────────────────────────────────────────────────────────
    //  Generate Summary
    // ────────────────────────────────────────────────────────────

    public function summarize(string $text, string $type = 'detailed'): ?string
    {
        $prompts = [
            'brief'         => 'Write a brief 3-5 sentence overview summary of this document.',
            'detailed'      => 'Write a detailed, in-depth summary of this document, covering all major sections and points.',
            'comprehensive' => 'Write a comprehensive summary covering all key information, arguments, data, and conclusions in this document.',
            'keypoints'     => 'Extract and list the key points and main takeaways from this document as a numbered or bulleted list.',
            'technical'     => 'Write a technical summary focusing on technical details, specifications, methods, and data from this document.',
            'simple'        => 'Write a simple, easy-to-understand summary of this document using plain language suitable for a general audience.',
            'chapterwise'   => 'Summarize this document chapter by chapter or section by section, clearly labeling each part.',
            'abstract'      => 'Write an academic-style abstract for this document, covering purpose, methods, findings, and conclusion.',
        ];

        if (!array_key_exists($type, $prompts)) {
            $type = 'detailed';
        }

        $instruction = $prompts[$type];
        $truncated   = mb_substr($text, 0, 12000);

        $messages = [
            [
                'role'    => 'system',
                'content' => 'You are an expert document summarizer. ' . $instruction . ' Be clear, structured, and accurate.',
            ],
            [
                'role'    => 'user',
                'content' => "Document text:\n\n" . $truncated,
            ],
        ];

        $result = $this->call($messages, 1500);
        return $result ? $this->markdownToHtml($result) : null;
    }


    // ────────────────────────────────────────────────────────────
    //  Generate Q&A Pairs
    // ────────────────────────────────────────────────────────────

    public function generateQA(string $fullText, int $count = 5): ?array
    {
        $messages = [
            [
                'role'    => 'system',
                'content' => 'You are an expert at creating educational Q&A pairs. Return ONLY valid JSON array, no extra text.',
            ],
            [
                'role'    => 'user',
                'content' => "Create exactly {$count} question and answer pairs from this document. Return as JSON array:\n" .
                             '[{"question": "...", "answer": "..."}]' .
                             "\n\nDocument:\n\n{$fullText}",
            ],
        ];

        $result = $this->call($messages, 2000);
        return $result ? $this->parseJsonResponse($result) : null;
    }


    // ────────────────────────────────────────────────────────────
    //  Generate MCQ Quiz
    // ────────────────────────────────────────────────────────────

    public function generateMCQ(string $fullText, int $count = 5): ?array
    {
        $messages = [
            [
                'role'    => 'system',
                'content' => 'You are an expert quiz maker. Return ONLY valid JSON array, no extra text.',
            ],
            [
                'role'    => 'user',
                'content' => "Create exactly {$count} multiple choice questions from this document. " .
                             "Each question must have 4 options (A, B, C, D) and one correct answer. Return as JSON array:\n" .
                             '[{"question":"...","options":{"A":"...","B":"...","C":"...","D":"..."},"answer":"A","explanation":"..."}]' .
                             "\n\nDocument:\n\n{$fullText}",
            ],
        ];

        $result = $this->call($messages, 3000);
        return $result ? $this->parseJsonResponse($result) : null;
    }


    // ────────────────────────────────────────────────────────────
    //  Generate True/False Quiz
    // ────────────────────────────────────────────────────────────

    public function generateTrueFalse(string $fullText, int $count = 5): ?array
    {
        $messages = [
            [
                'role'    => 'system',
                'content' => 'You are an expert quiz maker. Return ONLY valid JSON array, no extra text.',
            ],
            [
                'role'    => 'user',
                'content' => "Create exactly {$count} true/false questions from this document. Return as JSON array:\n" .
                             '[{"question":"...","answer":true,"explanation":"..."}]' .
                             "\n\nDocument:\n\n{$fullText}",
            ],
        ];

        $result = $this->call($messages, 2000);
        return $result ? $this->parseJsonResponse($result) : null;
    }


    // ────────────────────────────────────────────────────────────
    //  Helper — Strip markdown fences & decode JSON
    // ────────────────────────────────────────────────────────────

    private function parseJsonResponse(string $result): ?array
    {
        $result = preg_replace('/```json\s*/i', '', $result);
        $result = preg_replace('/```\s*/i',     '', $result);
        $result = trim($result);

        $parsed = json_decode($result, true);
        return is_array($parsed) ? $parsed : null;
    }
}
