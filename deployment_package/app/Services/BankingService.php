<?php

namespace App\Services;

use Exception;

class BankingService
{
    /**
     * Fetch bank details by IFSC code
     * 
     * @param string $ifsc
     * @return array|null
     */
    public function getBankDetailsByIFSC(string $ifsc): ?array
    {
        if (empty($ifsc)) {
            return null;
        }

        $curl = \curl_init();
        $url = 'https://bank-apis.justinclicks.com/API/V1/IFSC/' . \strtoupper($ifsc) . '/';

        \curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 5, // Set a reasonable timeout
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ]);

        $response = \curl_exec($curl);
        $err = \curl_error($curl);
        \curl_close($curl);

        if ($err) {
            return null;
        }

        $data = \json_decode($response, true);

        if (isset($data['IFSC'])) {
            return [
                'bank_name' => $data['BANK'] ?? '',
                'ifsc' => $data['IFSC'] ?? '',
                'micr' => $data['MICR'] ?? '',
                'branch' => $data['BRANCH'] ?? '',
                'address' => $data['ADDRESS'] ?? '',
                'city' => $data['CITY'] ?? '',
                'district' => $data['DISTRICT'] ?? '',
                'state' => $data['STATE'] ?? '',
            ];
        }

        return null;
    }
}
