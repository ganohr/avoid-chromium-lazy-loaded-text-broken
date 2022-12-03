@echo off
setlocal

for /f "delims=" %%a in (version.txt) do (
	set "version=%%a"
)

echo VERSION
echo %version%

set "outpath=.\tags\%version%"
mkdir %outpath%\

rem copy *.md %outpath%\
copy *.txt %outpath%\
copy *.css %outpath%\
copy *.js %outpath%\
copy *.php %outpath%\
del %outpath%\version.txt

del /F /Q /S .\trunk\
xcopy %outpath%\ .\trunk\

cd %outpath%

set "zipfile=..\..\release\avoid-the-chromium-lazy-loading-broken-characters-bug-%version%.zip"
del %zipfile%

tar -a -c -f %zipfile% *

set "basefile=..\..\release\avoid-the-chromium-lazy-loading-broken-characters-bug.zip"
del %basefile%

copy %zipfile% %basefile%

endlocal
pause
echo on
