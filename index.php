<?php
header('Content-Type: text/html; charset=UTF-8');

if(isset($_GET['username'])) {
    $username = trim($_GET['username']);
    $bearerToken = 'AAAAAAAAAAAAAAAAAAAAAE0gzAEAAAAApEW9wxUgkEQefjHlRkBj5PT4VJs%3DDC2ubJRUxV0lEAkCH0D7p1Dc1DbVaQXZrlxLC1l4N9d3wpaARv'; // تأكد من استبدال هذا
    
    function getTwitterData($url, $token) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if($httpCode !== 200) {
            throw new Exception('فشل في جلب البيانات من تويتر');
        }
        
        return json_decode($response, true);
    }

    try {
        $userData = getTwitterData("https://api.twitter.com/2/users/by/username/$username?user.fields=created_at,description,location,public_metrics,verified", $bearerToken);
        
        if(isset($userData['errors'])) {
            throw new Exception($userData['errors'][0]['detail']);
        }

        if(!isset($userData['data'])) {
            throw new Exception('الحساب غير موجود');
        }

        $output = [
            'name' => htmlspecialchars($userData['data']['name']),
            'username' => htmlspecialchars($userData['data']['username']),
            'created_at' => date('d/m/Y', strtotime($userData['data']['created_at'])),
            'location' => isset($userData['data']['location']) ? htmlspecialchars($userData['data']['location']) : "غير محدد",
            'description' => isset($userData['data']['description']) ? htmlspecialchars($userData['data']['description']) : "لا يوجد وصف",
            'followers_count' => number_format($userData['data']['public_metrics']['followers_count']),
            'verified' => $userData['data']['verified'] ? 'نعم' : 'لا'
        ];

        echo <<<HTML
        <div class="user-card">
            <h2>{$output['name']}</h2>
            <div class="user-info">
                <p><span>@{$output['username']}</span></p>
                <p>🕓 تاريخ الإنشاء: <b>{$output['created_at']}</b></p>
                <p>📍 الموقع: {$output['location']}</p>
                <p>👥 المتابعون: {$output['followers_count']}</p>
                <p>✅ الحساب موثق: {$output['verified']}</p>
            </div>
            <div class="bio">{$output['description']}</div>
        </div>
HTML;
        
    } catch(Exception $e) {
        echo "<div class='error'>❗ {$e->getMessage()}</div>";
    }
    exit();
}
?>
