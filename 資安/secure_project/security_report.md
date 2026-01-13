

## 5 個後端安全設計 

1.  **NoSQL Injection 防護**
    -   **實作位置**: `src/auth.php`, `public/home.php`
    -   **說明**: 針對 MongoDB 的特性，我們在後端強制將使用者輸入轉換為字串型別 (例如 `$username = (string)$username;`)。這能有效防止攻擊者傳入惡意陣列 (如 `['$ne' => null]`) 來繞過驗證邏輯，是防禦 Operator Injection 最直接有效的方法。

2.  **密碼安全儲存**
    -   **實作位置**: `src/auth.php`
    -   **說明**: 使用 PHP 目前最強大的 `password_hash()` 函式搭配 `PASSWORD_ARGON2ID` 演算法。此演算法具備記憶體硬化特性 (Memory-hard)，能極大程度增加駭客使用 GPU 進行暴力破解或字典攻擊的成本。絕不以明文儲存密碼。

3.  **防暴力破解機制**
    -   **實作位置**: `src/auth.php` (`check_brute_force` 函式)
    -   **說明**: 系統會記錄每個 IP 的登入失敗次數。若同一 IP 在短時間內連續失敗超過 5 次，系統將暫時封鎖該 IP 的登入請求。這能有效阻止自動化腳本的撞庫攻擊。
    -   **恢復**:http://localhost:8000/reset_login_attempts.php


4.  **CSRF (跨站請求偽造) 防護**
    -   **實作位置**: `src/security.php`, 所有 POST 表單
    -   **說明**: 每個表單都包含一個隨機生成的隱藏 `csrf_token`。後端在處理請求時 (`verify_csrf_token`) 會嚴格比對 Session 中的 Token 與表單提交的是否一致，確保請求是來自合法的網站頁面，而非外部惡意連結。

5.  **角色權限控管**
    -   **實作位置**: `public/admin.php`
    -   **說明**: 實作了嚴格的存取控制。在敏感頁面 (如管理後台) 載入前，後端會檢查 Session 中的 `role` 是否為 `admin`。若非管理員，系統會立即終止執行並將使用者導回首頁，防止越權存取 (Broken Access Control)。
    -   **admin強制登陸**:http://localhost/public/admin.php

## 3 個前端安全設計

1.  **XSS (跨站腳本) 防護 - 輸出編碼**
    -   **實作位置**: `templates/home.php`, `templates/admin.php`
    -   **說明**: 在將任何使用者產生的內容 (如使用者名稱、搜尋關鍵字) 顯示在網頁上之前，一律經過 `htmlspecialchars()` 處理。這會將 `<script>` 等特殊字元轉換為 HTML 實體，使其變成純文字顯示而不會被瀏覽器執行。

2.  **簡易 CAPTCHA (人機驗證)**
    -   **實作位置**: `public/register.php`
    -   **說明**: 在註冊頁面加入數學題目驗證 (如 "5 + 7 = ?")。雖然簡單，但能有效防止基礎的自動化註冊機器人 (Bots) 癱瘓資料庫或產生垃圾帳號。

3.  **密碼強度視覺化回饋**
    -   **實作位置**: `public/js/script.js`
    -   **說明**: 使用 JavaScript 即時分析使用者輸入的密碼複雜度 (長度、數字、符號)，並以顏色條 (紅/黃/綠) 給予回饋。這屬於「安全可用性」設計，引導使用者在高強度的後端驗證介入前，主動設定更安全的密碼。

## 2 個未來優化規劃 (Future Optimizations)

1.  **導入多因素驗證 (MFA / 2FA)**
    -   **規劃**: 目前依賴單一密碼驗證。未來可整合 Google Authenticator (TOTP) 或 Email OTP。在使用者登入成功後，要求輸入第二層動態密碼。這能確保即使密碼外洩，攻擊者若無使用者的手機或信箱，仍無法登入系統。

2.  **安全傳輸層 (HTTPS) 與 Cookie 屬性強化**
    -   **規劃**: 目前開發環境使用 HTTP。在正式部署時，應全面強制使用 HTTPS (TLS/SSL)。並在 PHP 的 `setcookie` 設定中加上 `Secure` (僅限 HTTPS 傳輸) 與 `SameSite=Strict` (限制跨站傳送) 屬性，進一步強化 Session ID 不被竊取或濫用。
