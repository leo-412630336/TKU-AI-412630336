# 專案開發思路與執行紀錄 (Development Log)

本文件紀錄了整個「期末資安專案」的開發思路、決策過程以及實際執行的步驟。

## 1. 需求分析 (Requirements Analysis)
- **輸入來源**：使用者提供了一個 `新增 文字文件.txt`，內容包含 SQL Injection 的攻擊範例以常見的資安防護術語 (Prepared Statements, CAPTCHA 等)。
- **核心目標**：建立一個 Web 應用程式，實際展示如何防禦這些攻擊。
- **技術決策**：
  - 參考文件中包含 PHP 程式碼，因此初步決定使用 **PHP** 作為後端語言。
  - 為了展示最經典的 SQL Injection 防護，最初選擇使用 **MySQL** 資料庫。

## 2. 第一階段：PHP + MySQL 實作 (Phase 1)
在此階段，我的思路是先快速搭建一個功能完整的原型，確保資安機制到位。

### 架構設計
- 採用 **MVC 模式** (Model-View-Controller) 的變體，將邏輯 (`public/login.php`) 與視圖 (`templates/login.php`) 分離，並將設定 (`config/`) 與核心功能 (`src/`) 獨立。

### 關鍵資安實作
1.  **身分驗證 (`src/auth.php`)**：
    -   使用 `password_hash(..., PASSWORD_ARGON2ID)` 進行密碼雜湊，這是目前業界推薦的標準。
    -   實作 `check_brute_force` 函數，透過記錄 IP 的失敗次數來防止暴力破解。
2.  **資料庫安全 (`config/db.php`)**：
    -   使用 **PDO** (PHP Data Objects)。
    -   設定 `PDO::ATTR_EMULATE_PREPARES => false`，這是一個重要的細節，確保資料庫層級的 Prepared Statements 真正生效。
3.  **SQL Injection 防護**：
    -   在登入與搜尋功能中，堅持使用 `prepare()` 和 `execute()` 綁定參數。
4.  **XSS 防護**：
    -   建立 `sanitize_input()` 輔助函數。
    -   在前端輸出的每一個變數都包裹 `htmlspecialchars()`，防止腳本注入。

## 3. 第二階段：遷移至 MongoDB (Phase 2)
使用者提出希望改用 **MongoDB**。這是一個重大的架構變更，需要重新思考資料存取層的安全性。

### 遷移思路
- **連線方式**：PHP 連接 MongoDB 有兩種主流方式 (1) `MongoDB\Client` (Composer Library) (2) `MongoDB\Driver\Manager` (原生 Extension)。考慮到使用者環境可能不複雜，我選擇了相容性較高的 **原生驅動** 寫法，或是建議使用者安裝 Extension。
- **NoSQL Injection 風險**：與 SQL 不同，MongoDB 的注入通常涉及傳入「操作符」 (如 `$ne`, `$gt`)。
    -   *例如*：如果攻擊者傳送 `user[$ne]=null`，查詢可能變成「找出使用者名稱**不等於 null** 的人」，導致繞過驗證。

### 防護策略修正
-   在 `src/auth.php` 和 `public/home.php` 中，我加入了**強制轉型**策略：
    ```php
    $username = (string)$username;
    ```
    這行程式碼非常關鍵。它確保了無論攻擊者傳什麼 (例如陣列)，到了資料庫查詢層都只會被當作一個普通的字串，從而徹底封殺 Operator Injection。

## 4. 執行總結 (Execution Summary)
我依序完成了以下工作：
1.  **規劃 (Planning)**：撰寫 `task.md` 與 `implementation_plan.md`，確認架構。
2.  **建置 (Setup)**：建立 `secure_project` 目錄結構。
3.  **MySQL 實作**：撰寫 `schema.sql`, `auth.php`, `login.php` 等，完成基礎版。
4.  **遷移 (Migration)**：
    -   移除 `schema.sql` (MongoDB 不需要)。
    -   重寫 `config/db.php` 改用 MongoDB 連線字串。
    -   重寫 `src/auth.php` 將 SQL 語法改為 MongoDB Query/BulkWrite。
5.  **文件 (Documentation)**：更新 `walkthrough.md` 教學文件，確保使用者知道如何設定 MongoDB Extension。

## 5. 檔案清單
最終專案包含：
- `config/db.php`: 資料庫連線
- `public/`: 網頁入口 (index, login, register, home) 與靜態資源 (css, js)
- `src/`: 後端核心 (auth, security)
- `templates/`: HTML 模板

這個過程展示了從傳統關聯式資料庫 (SQL) 轉向文件式資料庫 (NoSQL) 時，資安防護思維的轉變：從「防止語法拼接」轉變為「防止型別與操作符濫用」。
