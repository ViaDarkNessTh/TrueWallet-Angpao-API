<?php
session_start();

require_once "api.php";

$message = '';
$success = false;
$amount = '';
$phone = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['url']) && isset($_POST['phone'])) {
        $url = $_POST['url'];
        $phone = $_POST['phone'];
        
        // Validate URL
        if (empty($url) || filter_var($url, FILTER_VALIDATE_URL) === false || strpos($url, '?v=') === false) {
            $message = "รูปแบบ URL ไม่ถูกต้อง";
        } 
        // Validate phone number (Thai format: 10 digits starting with '0')
        elseif (empty($phone) || !preg_match('/^0[0-9]{9}$/', $phone)) {
            $message = "รูปแบบเบอร์โทรศัพท์ไม่ถูกต้อง ต้องเป็นเบอร์ไทย 10 หลัก";
        } 
        else {
            $tc = new topup();
            $vc = $tc->giftcode($url, $phone);
            
            if ($vc && isset($vc['status']['code'])) {
                if ($vc['status']['code'] != 'SUCCESS') {
                    $message = 'ไม่พบซองนี้ในระบบหรือใช้งานไปแล้ว ' . $vc['status']['code'];
                } else {
                    $success = true;
                    $amount = isset($vc['data']['voucher']['amount_baht']) ? $vc['data']['voucher']['amount_baht'] : '0';
                    $message = "รับซองของขวัญสำเร็จ! จำนวนเงิน $amount บาท เข้าสู่บัญชี Truemoney Wallet เบอร์ $phone";
                }
            } else {
                $message = "เกิดข้อผิดพลาด ไม่สามารถเชื่อมต่อกับระบบ Truemoney ได้";
            }
        }
    } else {
        $message = "กรุณากรอกข้อมูลให้ครบถ้วน";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import "https://fonts.googleapis.com/css?family=Kanit";

        * {
            font-family: "Kanit", sans-serif;
        }
        
        body {
            background-color: #f0f2f5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        main {
            flex: 1;
        }
        
        .wallet-form {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            max-width: 500px;
            width: 100%;
        }
        
        .btn-submit {
            background-color: #ff6700;
            color: white;
            border-radius: 5px;
            padding: 0.75rem 1.5rem;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            background-color: #e05d00;
        }
        
        .input-field {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 0.75rem;
            width: 100%;
        }
    </style>
    <title>ทดสอบระบบรับซองของขวัญ Truemoney Wallet</title>
</head>
<body>
    <header class="bg-orange-500 text-white p-4">
        <div class="container mx-auto">
            <h1 class="text-2xl font-bold">ระบบทดสอบรับซองของขวัญ Truemoney Wallet</h1>
        </div>
    </header>
    
    <main class="container mx-auto py-8 px-4 flex justify-center items-center">
        <div class="wallet-form">
            <div class="text-center mb-6">
                <img src="https://www.truemoney.com/wp-content/uploads/2022/01/truemoney-wallet-logo.png" alt="TrueMoney Wallet" class="h-16 mx-auto mb-4">
                <h2 class="text-xl font-bold text-gray-800">ระบบรับซองของขวัญ</h2>
                <p class="text-gray-600">กรอกเบอร์โทรศัพท์และลิงก์ซองของขวัญเพื่อรับเงิน</p>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="mb-4 p-3 rounded <?= $success ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>
            
            <form id="topupForm" method="POST" action="">
                <div class="mb-4">
                    <label for="phone" class="block mb-2 text-gray-700">เบอร์โทรศัพท์ Truemoney Wallet</label>
                    <input type="text" id="phone" name="phone" placeholder="0812345678" class="input-field" value="<?= htmlspecialchars($phone) ?>" required>
                    <p class="text-xs text-gray-500 mt-1">* เบอร์โทรศัพท์ที่ลงทะเบียน Truemoney Wallet</p>
                </div>
                
                <div class="mb-6">
                    <label for="url" class="block mb-2 text-gray-700">ลิงก์ซองของขวัญ</label>
                    <input type="text" id="url" name="url" placeholder="https://gift.truemoney.com/campaign/?v=..." class="input-field" required>
                    <p class="text-xs text-gray-500 mt-1">* ลิงก์ที่ได้จากแอป Truemoney Wallet</p>
                </div>
                
                <div class="text-center">
                    <button type="submit" class="btn-submit">รับซองของขวัญ</button>
                </div>
            </form>
            
            <div class="mt-6 text-center text-gray-600 text-sm">
                <p>การทดสอบนี้ใช้สำหรับการพัฒนาระบบเท่านั้น</p>
            </div>
        </div>
    </main>
    
    <footer class="bg-gray-800 text-white p-4 text-center">
        <p>ระบบทดสอบรับซองของขวัญ Truemoney Wallet &copy; <?= date('Y') ?></p>
    </footer>

    <script>
    $(document).ready(function() {
        $('#phone').on('input', function() {
            let value = $(this).val().replace(/[^0-9]/g, '');
            if (value.length > 10) {
                value = value.substring(0, 10);
            }
            $(this).val(value);
        });
        
        <?php if ($success): ?>
        // Show success message with SweetAlert2
        Swal.fire({
            title: 'สำเร็จ!',
            text: 'รับซองของขวัญสำเร็จ จำนวน <?= $amount ?> บาท',
            icon: 'success',
            confirmButtonText: 'ตกลง',
            confirmButtonColor: '#ff6700'
        });
        <?php endif; ?>
    });
    </script>
</body>
</html>