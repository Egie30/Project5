@echo off
set /p keyword=Please input the keyword :

findstr /m /s /C:"%keyword%" *.php > "keywords.%keyword%.txt"

if %errorlevel%==0 (
	echo Found! logged files into "keywords.%keyword%.txt"
) else (
	echo No matches found
)

pause