<?php

namespace App\Services\Legal;

use App\Services\AI\AIGateway;
use \App\Traits\ServiceTenantTrait;

class LegalAIService
{
    use \App\Traits\ServiceTenantTrait;

    protected LegalDocumentService $docService;
    protected ?AIGateway $aiGateway;

    public function __construct(?LegalDocumentService $docService = null)
    {
        $this->docService = $docService ?? new LegalDocumentService();
        $this->aiGateway = $this->initAIGateway();
    }

    private function initAIGateway(): ?AIGateway
    {
        try {
            if (class_exists('App\Services\AI\AIGateway')) {
                return AIGateway::getInstance();
            }
        } catch (\Exception $e) {
            error_log('LegalAIService::initAIGateway error: ' . $e->getMessage());
        }
        return null;
    }

    public function generateDocument(int $promptId, array $mergeData = []): array
    {
        try {
            $prompt = $this->docService->getAiPromptById($promptId);
            if (!$prompt) {
                return ['success' => false, 'error' => 'AI prompt template not found'];
            }

            $promptText = $prompt['prompt_template'];
            $promptText = $this->fillMergeFields($promptText, $mergeData);

            if ($this->aiGateway) {
                $result = $this->aiGateway->process('generate_document', [
                    'prompt' => $promptText,
                    'temperature' => (float)($prompt['temperature'] ?? 0.30),
                    'max_tokens' => (int)($prompt['max_tokens'] ?? 2048),
                ]);

                $content = '';
                if (!empty($result['success']) && !empty($result['response'])) {
                    $content = $result['response'];
                } elseif (!empty($result['text'])) {
                    $content = $result['text'];
                } elseif (!empty($result['content'])) {
                    $content = $result['content'];
                } else {
                    $content = $this->generateFallbackDocument($prompt, $mergeData);
                }
            } else {
                $content = $this->generateFallbackDocument($prompt, $mergeData);
            }

            return [
                'success' => true,
                'content' => $content,
                'title' => $prompt['name'] . ' - ' . ($mergeData['customer_name'] ?? date('d/m/Y')),
                'prompt_name' => $prompt['name'],
                'category' => $prompt['document_category']
            ];
        } catch (\Exception $e) {
            error_log('LegalAIService::generateDocument error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function generateFromCustomPrompt(string $promptText, array $mergeData = [], float $temperature = 0.30, int $maxTokens = 2048): array
    {
        try {
            $promptText = $this->fillMergeFields($promptText, $mergeData);

            if ($this->aiGateway) {
                $result = $this->aiGateway->process('generate_document', [
                    'prompt' => $promptText,
                    'temperature' => $temperature,
                    'max_tokens' => $maxTokens,
                ]);

                if (!empty($result['success']) && !empty($result['response'])) {
                    return ['success' => true, 'content' => $result['response']];
                }
                if (!empty($result['text'])) {
                    return ['success' => true, 'content' => $result['text']];
                }
                if (!empty($result['content'])) {
                    return ['success' => true, 'content' => $result['content']];
                }
            }

            return ['success' => false, 'error' => 'AI generation failed or unavailable'];
        } catch (\Exception $e) {
            error_log('LegalAIService::generateFromCustomPrompt error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function suggestClauses(string $context): array
    {
        try {
            if ($this->aiGateway) {
                $result = $this->aiGateway->process('suggest_clauses', [
                    'prompt' => "Suggest relevant legal clauses for the following real estate context in India:\n\n{$context}\n\nReturn as a JSON array of clause objects with 'title' and 'content' fields, suitable for a legal clause library.",
                    'temperature' => 0.20,
                    'max_tokens' => 2048,
                ]);
                if (!empty($result['response'])) {
                    $parsed = json_decode($result['response'], true);
                    if (is_array($parsed)) {
                        return ['success' => true, 'clauses' => $parsed];
                    }
                }
            }
            return ['success' => false, 'error' => 'Could not generate suggestions'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function generateFallbackDocument(array $prompt, array $data): string
    {
        $coName = $data['company_name'] ?? 'APS Dream Home';
        $coAddr = $data['company_address'] ?? '[Company Address]';
        $custName = $data['customer_name'] ?? '[Customer Name]';
        $custAddr = $data['customer_address'] ?? '[Customer Address]';
        $date = date('d/m/Y');
        $category = $prompt['document_category'] ?? 'general';
        $promptName = $prompt['name'] ?? 'Legal Document';
        $plotNo = $data['plot_no'] ?? '';
        $colonyName = $data['colony_name'] ?? '';

        $body = $this->getFallbackBody($category, $data);

        return <<<HTML
<div style="font-family: 'Times New Roman', serif; max-width: 800px; margin: 0 auto; padding: 40px;">
    <div style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 15px;">
        <h1 style="font-size: 24px; margin: 0;">{$coName}</h1>
        <p style="margin: 5px 0;">{$coAddr}</p>
        <hr style="border: none; border-top: 1px solid #000; margin: 10px 0;">
        <h2 style="font-size: 18px; margin: 0;">{$promptName}</h2>
    </div>
    <p style="text-align: right; font-size: 14px;">Date: {$date}</p>
    <p style="font-size: 14px;">To,<br><strong>{$custName}</strong><br>{$custAddr}</p>
    <p style="font-size: 14px;"><strong>Subject:</strong> {$promptName} - {$plotNo} {$colonyName}</p>
    <div style="margin-top: 20px; line-height: 1.8;">
        {$body}
    </div>
    <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #ccc;">
        <table style="width: 100%; font-size: 14px;">
            <tr>
                <td style="text-align: center;">
                    <p>_________________________</p>
                    <p><strong>For {$coName}</strong></p>
                    <p>Authorised Signatory</p>
                </td>
                <td style="text-align: center;">
                    <p>_________________________</p>
                    <p><strong>{$custName}</strong></p>
                    <p>Customer</p>
                </td>
            </tr>
        </table>
    </div>
    <div style="margin-top: 20px; font-size: 11px; color: #666; border-top: 1px solid #ddd; padding-top: 10px;">
        <p>This is a computer-generated document. Signature verification recommended.</p>
        <p>Document generated on: {$date}</p>
    </div>
</div>
HTML;
    }

    private function getFallbackBody(string $category, array $data): string
    {
        $method = 'get' . str_replace('-', '', ucwords($category, '-')) . 'Fallback';
        if (method_exists($this, $method)) {
            return $this->$method($data);
        }
        return $this->getGeneralFallback($data);
    }

    private function getBookingDocsFallback(array $data): string
    {
        $plotNo = $data['plot_no'] ?? '___';
        $colony = $data['colony_name'] ?? '___';
        $plotPrice = $data['plot_price'] ?? '[Amount]';
        $bookingAmt = $data['booking_amount'] ?? '[Amount]';
        $bookingDate = $data['booking_date'] ?? date('d/m/Y');
        return "<h3>1. Booking & Payment Terms</h3>
    <p>The Customer has booked Plot No. <strong>{$plotNo}</strong> in <strong>{$colony}</strong> Colony for a total consideration of Rs.{$plotPrice}. The booking amount of Rs.{$bookingAmt} has been received on {$bookingDate}.</p>
    <h3>2. Payment Schedule</h3>
    <p>The remaining amount shall be paid as per the payment plan agreed between the parties. Delay in payment shall attract interest at 18% per annum on the overdue amount.</p>
    <h3>3. Possession</h3>
    <p>The Developer shall Endeavour to deliver possession within [X] months from the date of this agreement, subject to force majeure and statutory approvals.</p>
    <h3>4. Cancellation</h3>
    <p>Cancellation shall be governed by the Cancellation Policy in force. Refund shall be processed within 45 days from the date of cancellation after deducting applicable charges.</p>
    <h3>5. Dispute Resolution</h3>
    <p>Any dispute shall be resolved through arbitration in accordance with the Arbitration and Conciliation Act, 1996. The courts in [City] shall have exclusive jurisdiction.</p>";
    }

    private function getAssociateAgreementsFallback(array $data): string
    {
        $assocName = $data['associate_name'] ?? '[Associate Name]';
        $assocCode = $data['associate_code'] ?? '___';
        $assocLevel = $data['associate_level'] ?? 'Associate';
        $commRate = $data['commission_rate'] ?? '[X]';
        return "<h3>1. Appointment</h3>
    <p><strong>{$assocName}</strong> (Code: {$assocCode}) is hereby appointed as an Associate of APS Dream Home at the level of <strong>{$assocLevel}</strong>.</p>
    <h3>2. Responsibilities</h3>
    <p>The Associate shall:
    <ul>
        <li>Promote and market the company's real estate projects</li>
        <li>Procure genuine and interested customers</li>
        <li>Maintain professional conduct and ethical practices</li>
        <li>Attend training sessions and company meetings</li>
        <li>Submit daily reports of sales activities</li>
    </ul></p>
    <h3>3. Commission Structure</h3>
    <p>The Associate shall be entitled to commission at the rate of {$commRate}% on successful referrals as per company policy. Commission is payable within 45 days of payment realization.</p>
    <h3>4. Non-Compete</h3>
    <p>During this Agreement and for 12 months thereafter, the Associate shall not engage with any competing real estate business within 25 km radius of any company project.</p>
    <h3>5. Termination</h3>
    <p>Either party may terminate this agreement with 30 days notice. The company may terminate immediately for misconduct or violation of company policy.</p>";
    }

    private function getPoliciesTermsFallback(array $data): string
    {
        return "<h3>1. Cancellation Policy</h3>
    <p>Bookings may be cancelled by submitting a written request. Refund amounts shall be calculated as follows:
    <ul>
        <li>Within 15 days of booking: 90% refund (10% deduction)</li>
        <li>Within 30 days of booking: 75% refund (25% deduction)</li>
        <li>After 30 days: Subject to company discretion</li>
    </ul></p>
    <h3>2. Transfer/Relocation Policy</h3>
    <p>Requests for transfer between colonies shall be considered subject to availability and differential payment. A transfer fee as prescribed by the company from time to time shall apply.</p>
    <h3>3. Refund Processing</h3>
    <p>All refunds shall be processed within 45 working days from the date of approval. Refunds shall be made via bank transfer to the customer's registered bank account.</p>";
    }

    private function getColonyDocumentsFallback(array $data): string
    {
        $fromColony = $data['transfer_from_colony'] ?? '[Original Colony]';
        $toColony = $data['transfer_to_colony'] ?? '[New Colony]';
        $transDate = $data['transfer_date'] ?? date('d/m/Y');
        return "<h3>1. Transfer of Plot</h3>
    <p>This Deed witnesses the transfer of plot from <strong>{$fromColony}</strong> to <strong>{$toColony}</strong> effective {$transDate}.</p>
    <h3>2. Terms of Transfer</h3>
    <p>The original booking terms and conditions shall continue to apply. Any difference in plot value shall be settled as per company policy.</p>
    <h3>3. Indemnity</h3>
    <p>The customer hereby indemnifies the company against any claims arising from this transfer.</p>";
    }

    private function getLoanDocumentsFallback(array $data): string
    {
        $loanAmt = $data['loan_amount'] ?? '[Amount]';
        $intRate = $data['interest_rate'] ?? '[X]';
        $tenure = $data['tenure'] ?? '[X]';
        $emi = $data['emi_amount'] ?? '[Amount]';
        return "<h3>1. Loan Details</h3>
    <p>Loan Amount: Rs.{$loanAmt}<br>
    Interest Rate: {$intRate}% per annum<br>
    Tenure: {$tenure} months<br>
    EMI: Rs.{$emi} per month</p>
    <h3>2. Repayment Terms</h3>
    <p>The borrower shall pay monthly installments on or before the 10th of each month. Late payment penalty of 18% per annum shall apply on overdue amounts.</p>
    <h3>3. Default & Foreclosure</h3>
    <p>In case of default for 3 consecutive months, the company reserves the right to foreclose the loan and recover the outstanding amount along with applicable penalties.</p>";
    }

    private function getLegalNoticesFallback(array $data): string
    {
        $pendingAmt = $data['pending_amount'] ?? '[Amount]';
        $dueDate = $data['due_date'] ?? '[Due Date]';
        $bookingId = $data['booking_id'] ?? '[Booking ID]';
        return "<h3>Notice of Default / Demand</h3>
    <p>This is a formal notice demanding payment of the outstanding amount of Rs.{$pendingAmt} which was due on {$dueDate}.</p>
    <h3>Payment Instructions</h3>
    <p>Please make the payment within 15 days from the date of this notice. Failure to comply shall result in further legal action including cancellation of booking and forfeiture of earnest money as per the terms of agreement.</p>
    <h3>Bank Details for Payment</h3>
    <p>Bank: [Bank Name]<br>
    Account: [Account Number]<br>
    IFSC: [IFSC Code]<br>
    Reference: {$bookingId}</p>";
    }

    private function getFormsApplicationsFallback(array $data): string
    {
        $custName = $data['customer_name'] ?? '[Customer Name]';
        return "<h3>Declaration / Undertaking</h3>
    <p>I, <strong>{$custName}</strong>, hereby declare that:</p>
    <ol>
        <li>The information provided above is true and correct to the best of my knowledge.</li>
        <li>I have read and understood the terms and conditions of the booking.</li>
        <li>I agree to abide by all the rules and regulations of the colony.</li>
        <li>I undertake to make all payments as per the schedule.</li>
    </ol>
    <p>I have affixed my signature below in acceptance of the above terms.</p>";
    }

    private function getKycDocumentsFallback(array $data): string
    {
        $custName = $data['customer_name'] ?? '[Customer Name]';
        return "<h3>KYC Document Verification Form</h3>
    <p>Customer Name: <strong>{$custName}</strong></p>
    <h4>Documents Submitted:</h4>
    <ol>
        <li>Proof of Identity (PAN Card / Aadhaar Card / Voter ID / Passport)</li>
        <li>Proof of Address (Aadhaar / Utility Bill / Bank Statement / Passport)</li>
        <li>Passport Size Photographs (2 copies)</li>
        <li>Signature Proof</li>
    </ol>
    <p><strong>Declaration:</strong> I confirm that the documents submitted are genuine and original.</p>";
    }

    private function getGeneralFallback(array $data): string
    {
        $custName = $data['customer_name'] ?? '[Customer Name]';
        return "<h3>General Terms</h3>
    <p>This document is issued in connection with the business relationship between APS Dream Home and {$custName}.</p>
    <p>All terms and conditions as per the standard company policies shall apply.</p>
    <p>The parties agree to be bound by the terms outlined herein.</p>";
    }

    private function fillMergeFields(string $text, array $data): string
    {
        $replacements = [
            '{{customer_name}}' => $data['customer_name'] ?? '[Customer Name]',
            '{{customer_phone}}' => $data['customer_phone'] ?? '[Phone]',
            '{{customer_email}}' => $data['customer_email'] ?? '[Email]',
            '{{customer_address}}' => $data['customer_address'] ?? '[Address]',
            '{{plot_no}}' => $data['plot_no'] ?? '[Plot No]',
            '{{colony_name}}' => $data['colony_name'] ?? '[Colony Name]',
            '{{colony_address}}' => $data['colony_address'] ?? '[Colony Address]',
            '{{plot_area}}' => $data['plot_area'] ?? '[Area]',
            '{{plot_price}}' => $data['plot_price'] ?? '[Price]',
            '{{booking_date}}' => $data['booking_date'] ?? date('d/m/Y'),
            '{{booking_id}}' => $data['booking_id'] ?? '[Booking ID]',
            '{{booking_amount}}' => $data['booking_amount'] ?? '[Amount]',
            '{{payment_terms}}' => $data['payment_terms'] ?? '[Payment Terms]',
            '{{associate_name}}' => $data['associate_name'] ?? '[Associate Name]',
            '{{associate_code}}' => $data['associate_code'] ?? '[Code]',
            '{{associate_level}}' => $data['associate_level'] ?? '[Level]',
            '{{commission_rate}}' => $data['commission_rate'] ?? '[X]',
            '{{company_name}}' => $data['company_name'] ?? 'APS Dream Home',
            '{{company_address}}' => $data['company_address'] ?? '[Company Address]',
            '{{current_date}}' => date('d/m/Y'),
            '{{current_year}}' => date('Y'),
            '{{cancellation_date}}' => $data['cancellation_date'] ?? '[Cancellation Date]',
            '{{cancellation_reason}}' => $data['cancellation_reason'] ?? '[Reason]',
            '{{refund_amount}}' => $data['refund_amount'] ?? '[Refund Amount]',
            '{{transfer_from_colony}}' => $data['transfer_from_colony'] ?? '[From Colony]',
            '{{transfer_to_colony}}' => $data['transfer_to_colony'] ?? '[To Colony]',
            '{{transfer_date}}' => $data['transfer_date'] ?? '[Transfer Date]',
            '{{loan_amount}}' => $data['loan_amount'] ?? '[Loan Amount]',
            '{{interest_rate}}' => $data['interest_rate'] ?? '[Interest Rate]',
            '{{emi_amount}}' => $data['emi_amount'] ?? '[EMI]',
            '{{tenure}}' => $data['tenure'] ?? '[Tenure]',
            '{{due_date}}' => $data['due_date'] ?? '[Due Date]',
            '{{pending_amount}}' => $data['pending_amount'] ?? '[Pending Amount]',
        ];
        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }
}
