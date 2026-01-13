@echo off
echo ===========================================
echo   Starting Secure App (MongoDB Version)
echo ===========================================
echo.
echo [IMPORTANT] Please ensure your MongoDB server is running!
echo (You can start it by running 'mongod' in another terminal)
echo.
echo Starting PHP Server at http://localhost:8000 ...
echo.
"C:\Users\leo09\Documents\php\php.exe" -S localhost:8000 -t public
pause
