# Secure PHP Project (MongoDB Edition)

這是您課堂報告用的資安專案。以下是下次開啟專案的步驟。

## 1. 環境準備 (Prerequisites)
確保電腦已安裝以下軟體：
*   **PHP**: 路徑設定為 `C:\Users\leo09\Documents\php\php.exe`
*   **MongoDB**: 資料庫服務。

## 2. 啟動步驟 (How to Start)

### 步驟一：啟動資料庫
打開 "命令提示字元 (cmd)" 或 "PowerShell"，輸入以下指令並按下 Enter：
```cmd
mongod
```
*如果不確定是否已啟動，可以打開 MongoDB Compass 連線看看。*

### 步驟二：啟動網站伺服器
請直接雙擊專案資料夾中的 **`start_server.bat`** 檔案。
它會自動幫您執行 PHP 伺服器指令。

### 步驟三：開啟網頁
打開瀏覽器，網址輸入：
[http://localhost:8000](http://localhost:8000)

## 3. 專案文件說明
*   **`security_report.md`**: **期末報告用**，詳細的安全設計說明。
*   **`walkthrough.md`**: **Demo 用**，詳細的功能與弱點測試步驟 (如 SQL Injection)。
*   **`development_log.md`**: 開發日誌。
*   **`public/fix_admin.php`**: 修復管理員權限的小工具。
*   **`public/reset_login_attempts.php`**: 重置暴力破解封鎖的小工具。

祝您報告順利！
