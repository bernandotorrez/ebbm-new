<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiTestController extends Controller
{
    /**
     * Test SIMPEG API
     */
    public function simpegTest()
    {
        try {
            // Make HTTP request with basic auth
            $response = Http::withBasicAuth('apisimpeg', 'bismillah')
                ->withOptions([
                    'verify' => false, // Ignore SSL verification
                ])
                ->timeout(60)
                ->get('https://simpeg.basarnas.go.id/api/pegawai', [
                    'nip' => '198108192010121001'
                ]);

            // Check if request was successful
            if ($response->successful()) {
                $data = $response->json();
                
                return response()->json([
                    'success' => true,
                    'status' => $response->status(),
                    'data' => $data,
                ]);
            }

            // Handle error response
            return response()->json([
                'success' => false,
                'status' => $response->status(),
                'message' => 'API request failed',
                'error' => $response->body(),
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('SIMPEG API Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Exception occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test SIMPEG API with custom NIP
     */
    public function simpegTestByNip(Request $request)
    {
        $request->validate([
            'nip' => 'required|string',
        ]);

        try {
            $response = Http::withBasicAuth('apisimpeg', 'bismillah')
                ->withOptions([
                    'verify' => false,
                ])
                ->timeout(60)
                ->get('https://simpeg.basarnas.go.id/api/pegawai', [
                    'nip' => $request->nip
                ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'status' => $response->status(),
                    'data' => $response->json(),
                ]);
            }

            return response()->json([
                'success' => false,
                'status' => $response->status(),
                'message' => 'API request failed',
                'error' => $response->body(),
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('SIMPEG API Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Exception occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test SIMPEG API and display in browser
     */
    public function simpegTestView()
    {
        try {
            $response = Http::withBasicAuth('apisimpeg', 'bismillah')
                ->withOptions([
                    'verify' => false,
                ])
                ->timeout(60)
                ->get('https://simpeg.basarnas.go.id/api/pegawai', [
                    'nip' => '198108192010121001'
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                echo '<h1>SIMPEG API Test Result</h1>';
                echo '<h2>Status: ' . $response->status() . '</h2>';
                echo '<h3>Response Data:</h3>';
                echo '<pre>';
                print_r($data);
                echo '</pre>';
                
                echo '<h3>Raw JSON:</h3>';
                echo '<pre>';
                echo json_encode($data, JSON_PRETTY_PRINT);
                echo '</pre>';
            } else {
                echo '<h1>API Request Failed</h1>';
                echo '<p>Status: ' . $response->status() . '</p>';
                echo '<pre>';
                echo $response->body();
                echo '</pre>';
            }

        } catch (\Exception $e) {
            echo '<h1>Exception Occurred</h1>';
            echo '<p>' . $e->getMessage() . '</p>';
        }
    }
}
