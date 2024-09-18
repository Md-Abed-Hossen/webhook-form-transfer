<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FormController extends Controller
{
    public function handleWebhook(Request $request)
    {
        Log::info('NEW WEBHOOK CALL RECEIVED 🚀');
        Log::info('Webhook payload received:', $request->all());

        try {
            $formName = $this->extractFormName($request->input('answer.answers'));
            $formActive = $this->extractFormActive($request->input('answer.answers'));

            Log::info("Extracted Form Name: " . ($formName ?? 'null'));
            Log::info("Extracted Form Active: " . ($formActive === null ? 'null' : ($formActive ? 'true' : 'false')));

            if ($formName === null || $formActive === null) {
                Log::error('Invalid form data received');
                return response()->json(['message' => 'Invalid form data'], 400);
            }

            if (!$formActive) {
                Log::info("ℹ️ Form is not active. Skipping creation in 123FormBuilder.");
                return response()->json(['message' => 'Form is not active. Creation skipped.'], 200);
            }

            Log::info("📝 Attempting to create form on 123FormBuilder");
            $result = $this->createFormOn123FormBuilder($formName, $formActive);

            if ($result['success']) {
                Log::info("Form created successfully with ID: {$result['formId']}");
                return response()->json(['message' => 'Form processed successfully', 'formId' => $result['formId']], 200);
            } else {
                Log::error("Failed to create form: {$result['error']}");
                return response()->json(['message' => 'Failed to create form on 123FormBuilder', 'error' => $result['error']], 500);
            }
        } catch (\Exception $e) {
            Log::error('Exception in handleWebhook: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while processing the webhook', 'error' => $e->getMessage()], 500);
        }
    }

    private function extractFormName($answers)
    {
        Log::info("Extracting form name from answers:", $answers);
        foreach ($answers as $answer) {
            if ($answer['q'] === '66e477d4d1556070acc9b481') {
                Log::info("Form name found: " . $answer['t']);
                return $answer['t'];
            }
        }
        Log::warning("Form name not found in answers");
        return null;
    }

    private function extractFormActive($answers)
    {
        Log::info("Extracting form active status from answers:", $answers);
        foreach ($answers as $answer) {
            if ($answer['q'] === '66e477d4d1556070acc9b484') {
                $isActive = $answer['c'][0]['t'] === 'Yes';
                Log::info("Form active status found: " . ($isActive ? 'Yes' : 'No'));
                return $isActive;
            }
        }
        Log::warning("Form active status not found in answers");
        return null;
    }

    private function createFormOn123FormBuilder($formName, $formActive)
    {
        $apiKey = env('FORM_BUILDER_API_KEY');
        $apiUrl = 'https://api.123formbuilder.com/v2/forms';

        Log::info("Attempting to create form on 123FormBuilder - Name: $formName, Active: " . ($formActive ? 'Yes' : 'No'));

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post($apiUrl, [
                'name' => $formName,
                'active' => $formActive,
            ]);

            $responseData = $response->json();
            Log::info('123FormBuilder API Response:', $responseData);

            if ($response->successful() && isset($responseData['data']['id'])) {
                $formId = $responseData['data']['id'];
                return ['success' => true, 'formId' => $formId];
            } else {
                $errorMessage = $responseData['message'] ?? 'Unknown error';
                Log::error("123FormBuilder API Error: $errorMessage");
                return ['success' => false, 'error' => "API Error: $errorMessage"];
            }
        } catch (\Exception $e) {
            Log::error('Exception occurred while creating form on 123FormBuilder: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Exception occurred: ' . $e->getMessage()];
        }
    }
}