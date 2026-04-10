@echo off
REM Run Moodle code checker against this plugin
REM Usage: check.bat [path]  (defaults to current directory)

set PHP=C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe
set PHPCS=C:\dev\moodle\public\local\codechecker\vendor\bin\phpcs
set STANDARD=C:\dev\moodle\public\local\codechecker\vendor\moodlehq\moodle-cs\moodle\ruleset.xml

if "%~1"=="" (
    set TARGET=%~dp0
) else (
    set TARGET=%~1
)

"%PHP%" "%PHPCS%" --standard="%STANDARD%" --extensions=php "%TARGET%"
