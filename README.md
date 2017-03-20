# paypal_cakephp
##

Mục tiêu
- Kết nối cakephp 2.* với paypal API
- viết demo thanh toán bằng paypal payment và credit-card payment
##

Các bước tiến hành
- Tạo tài khoản sandbox (test) paypal
- Tạo REST API apps
- Kết nối cakePhp 2.* với PayPal-PHP-SDK
- Tạo demo thanh toán
##

Yêu cầu trước khi thực hiện
- Đã cài đặt composer https://getcomposer.org/
- Đã cài đặt Xampp (đối với máy windows)
- Đã cài đặt Cakephp 2.*

##

Thực hiện
- Tạo tài khoản sandbox paypal account tại đây
1. Đăng ký tài khoản paypal https://www.paypal.com/
2. Tạo tài khoảng sandbox theo hướng dẫn tại đây: https://developer.paypal.com/docs/classic/lifecycle/sb_create-accounts/
- Tạo REST API apps 
1. Tạo REST API apps theo hướng dẫn tại đây : https://developer.paypal.com/docs/integration/direct/make-your-first-call/#create-a-paypal-app
2. Get Access Token tại đây: https://developer.paypal.com/docs/integration/direct/make-your-first-call/#get-an-access-token
- Kết nối cakePHP 2.* với PayPal-PHP-SDK
1. PayPal-PHP-SDK là gì: http://paypal.github.io/PayPal-PHP-SDK/ là SDK được phát triển bới paypal.
2. Cài đặt PayPal-PHP-SDK trên cakePHP 2.*:
 2.2 Đến thư mục PayPal-PHP-SDK root\projectname\app trong command promot và gõ: composer require "paypal/rest-api-sdk-php:*" sẽ tự động cài đặt PayPal-PHP-SDK trên cakephp 2.*
 2.3 Trong thư muc app/Config/bootstrap.php thêm dòng lệnh để gọi autoload file giúp tự động kết nối vendor: App::import('Vendor', array('file' => 'autoload'));
- Tạo demo thanh toán
1. Tìm hiểu API thanh toán bằng paypal account và credit card : http://paypal.github.io/PayPal-PHP-SDK/sample/#payments
2. Tạo function để sử dụng nhiều lần xác nhận tài khoản paypal account: 
https://developer.paypal.com/docs/api/quickstart/environment/
3. Để kiểm tra những giao dịch đã được thực hiện thì truy cập tài khoản sandbox và theo dõi transaction tại đây: https://developer.paypal.com/developer/accounts/
