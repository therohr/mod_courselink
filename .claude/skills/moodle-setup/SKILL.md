# Moodle Plugin Setup
1. Identify plugin type from prefix (mod_, local_, block_)
2. Copy to correct path under /public/ (e.g., /public/mod/ for mod_ plugins)
3. Verify version.php exists and is intact
4. Run `php public/admin/cli/check_database_schema.php` to validate
