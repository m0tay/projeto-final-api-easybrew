URL_BASE="https://esan-tesp-ds-paw.web.ua.pt/tesp-ds-g24/aulas/8/api/users/login.php"

test-api:
	http POST ${URL_BASE} @api-tests/login.json

