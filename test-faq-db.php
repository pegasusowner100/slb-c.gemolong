<?php
require_once 'includes/db.php';

echo "Supabase Connected: " . ($supabaseConnected ? "✅ Ya" : "❌ Tidak") . "\n\n";

if ($supabaseConnected) {
    echo "--- Testing Insert ---\n";
    $testData = [
        'pertanyaan' => 'Pertanyaan Test ' . time(),
        'jawaban' => 'Jawaban Test',
        'urutan' => 99,
        'status' => 'draft',
        'nama_penanya' => 'Tester',
        'email_penanya' => 'test@example.com'
    ];
    $insertRes = supabaseInsert('faq', $testData);
    print_r($insertRes);

    if ($insertRes['success'] && !empty($insertRes['data'])) {
        $id = $insertRes['data'][0]['id'] ?? null;
        if ($id) {
            echo "\n--- Testing Update (ID: $id) ---\n";
            $updateData = [
                'pertanyaan' => 'Pertanyaan Test Updated ' . time(),
                'updated_at' => date('c')
            ];
            $updateRes = supabaseUpdate('faq', $updateData, $id);
            print_r($updateRes);

            echo "\n--- Testing Delete (ID: $id) ---\n";
            $deleteRes = supabaseDelete('faq', $id);
            print_r($deleteRes);
        }
    }
}
