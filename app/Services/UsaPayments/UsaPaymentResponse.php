<?php

namespace App\Services\UsaPayments;

use Illuminate\Http\Client\Response;

class UsaPaymentResponse
{
    public function __construct(
        private readonly ?string $response,
    ) {}

    public static function fromHttpResponse(Response $response): self
    {
        return new self($response->body());
    }

    public function getRawResponse(): ?string
    {
        return $this->response;
    }

    /**
     * @return array<string, mixed>
     */
    public function getParsedResponse(): array
    {
        if ($this->response === null || trim($this->response) === '') {
            return [];
        }

        if (str_starts_with(trim($this->response), '<?xml')) {
            $xml = simplexml_load_string($this->response, 'SimpleXMLElement', LIBXML_NOCDATA);
            if ($xml === false) {
                return [];
            }

            return json_decode(json_encode($xml), true) ?: [];
        }

        $parsedResponse = [];
        foreach (explode('&', $this->response) as $item) {
            $temp = explode('=', $item, 2);
            if (count($temp) === 2) {
                $parsedResponse[trim($temp[0])] = urldecode(trim($temp[1]));
            }
        }

        return $this->appendResponseCodeText($parsedResponse);
    }

    public function isApproved(): bool
    {
        $parsed = $this->getParsedResponse();

        return ($parsed['response'] ?? null) === '1';
    }

    /**
     * @param  array<string, mixed>  $parsedResponse
     * @return array<string, mixed>
     */
    private function appendResponseCodeText(array $parsedResponse): array
    {
        $parsedResponse['response_code_text'] = match ($parsedResponse['response_code'] ?? null) {
            '100' => 'Transaction was approved',
            '200' => 'Transaction was declined by processor',
            '201' => 'Do not honor',
            '202' => 'Insufficient funds',
            '203' => 'Over limit',
            '204' => 'Transaction not allowed',
            '220' => 'Incorrect payment information',
            '221' => 'No such card issuer',
            '222' => 'No card number on file with issuer',
            '223' => 'Expired card',
            '224' => 'Invalid expiration date',
            '225' => 'Invalid card security code',
            '226' => 'Invalid PIN',
            '240' => 'Call issuer for further information',
            '250' => 'Pick up card',
            '251' => 'Lost card',
            '252' => 'Stolen card',
            '253' => 'Fraudulent card',
            '260' => 'Declined with further instructions available. (See response text)',
            '261' => 'Declined-Stop all recurring payments',
            '262' => 'Declined-Stop this recurring program',
            '263' => 'Declined-Update cardholder data available',
            '264' => 'Declined-Retry in a few days',
            '300' => 'Transaction was rejected by gateway',
            '400' => 'Transaction error returned by processor',
            '410' => 'Invalid merchant configuration',
            '411' => 'Merchant account is inactive',
            '420' => 'Communication error',
            '421' => 'Communication error with issuer',
            '430' => 'Duplicate transaction at processor',
            '440' => 'Processor format error',
            '441' => 'Invalid transaction information',
            '460' => 'Processor feature not available',
            '461' => 'Unsupported card type',
            default => (string) ($parsedResponse['responsetext'] ?? ''),
        };

        return $parsedResponse;
    }
}
