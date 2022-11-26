@echo off
setlocal

for /f "delims=" %%a in (version.txt) do (
	set "version=%%a"
)

echo VERSION
echo %version%

set "outpath=.\trunk\%version%"
mkdir %outpath%\

rem copy *.md %outpath%\
copy *.txt %outpath%\
copy *.css %outpath%\
copy *.js %outpath%\
copy *.php %outpath%\
del %outpath%\version.txt

cd %outpath%

set "zipfile=..\..\release\avoid-chromium-lazy-loaded-text-broken-%version%.zip"
del %zipfile%

tar -a -c -f %zipfile% *

set "basefile=..\..\release\avoid-chromium-lazy-loaded-text-broken.zip"
del %basefile%

copy %zipfile% %basefile%

endlocal
pause
echo on
