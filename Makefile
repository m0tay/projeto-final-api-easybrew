URL_BASE="https://esan-tesp-ds-paw.web.ua.pt/tesp-ds-g24/aulas/8/"

test:
	http GET "${URL_BASE}/api/up.php"
	http POST "${URL_BASE}api/users/register.php" @tests/api/create.json
	http POST "${URL_BASE}api/users/login.php" @tests/api/login.json
	http POST "${URL_BASE}api/users/update.php" @tests/api/update.json
	http POST "${URL_BASE}api/users/validate_token.php" @tests/api/validate_token.json

