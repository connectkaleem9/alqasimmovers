#!/usr/bin/env bash
# End-to-end test of the dynamic features against a throwaway SQLite database.
#   usage: PHP=/path/to/php tests/e2e-local.sh <scratch-dir> <test-image> <test-video>
# Starts PHP's built-in server on dist/, runs every flow, then stops the server.
set -u
PHP="${PHP:-php}"
SCR="$1"; VID="$3"
# curl on Windows cannot read POSIX-style /d/... paths, so work from a copy
cp "$2" "$SCR/e2e-image.${2##*.}"; IMG="$SCR/e2e-image.${2##*.}"
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PORT=4181
BASE="http://localhost:$PORT"
JAR="$SCR/e2e-cookies.txt"
DB="$SCR/e2e.sqlite"
CFG="$SCR/e2e-config.php"
SETUP_KEY="test-setup-key-123"
pass=0; fail=0
ok()   { pass=$((pass+1)); printf '  ✓ %s\n' "$1"; }
bad()  { fail=$((fail+1)); printf '  ✗ %s\n' "$1"; }
check(){ if eval "$2"; then ok "$1"; else bad "$1"; fi; }

rm -f "$DB" "$DB-wal" "$DB-shm" "$JAR"
rm -rf "$ROOT/dist/uploads/projects"
cat > "$CFG" <<EOF
<?php return [
  'db' => ['driver' => 'sqlite', 'path' => '$DB'],
  'mail' => ['enabled' => false, 'to' => 'x@example.com', 'from' => 'x@example.com'],
  'limits' => ['per_ip_per_10min' => 5, 'min_seconds' => 3, 'review_gap_seconds' => 0],
  'ip_salt' => 'e2e',
  'admin' => ['setup_key' => '$SETUP_KEY'],
];
EOF

QASIM_CONFIG="$CFG" "$PHP" -S "localhost:$PORT" -t "$ROOT/dist" > "$SCR/e2e-server.log" 2>&1 &
SERVER=$!
trap 'kill $SERVER 2>/dev/null' EXIT
sleep 1.5

loc() { curl -s -o /dev/null -w '%{redirect_url}' "$@"; }
code(){ curl -s -o /dev/null -w '%{http_code}' "$@"; }
OLD=$(( ($(date +%s) - 60) * 1000 ))   # a form "opened" a minute ago

echo "Reviews (public)"
check "reviews page loads (200)"            '[ "$(code $BASE/reviews/)" = 200 ]'
check "empty state shown"                    'curl -s $BASE/reviews/ | grep -q "No written reviews yet"'
R=$(loc -X POST $BASE/form/review.php --data-urlencode "name=Ahmed Khan" --data-urlencode "email=ahmed.k@example.com" -d "rating=5&service=villa&lang=en&ts=$OLD" --data-urlencode "area=Dubai Marina" --data-urlencode "body=Very careful team, they wrapped everything and reassembled all the beds perfectly.")
check "valid review accepted -> thanks"      '[[ "$R" == *"/reviews/?thanks=1"* ]]'
check "review appears on the page"           'curl -s $BASE/reviews/ | grep -q "reassembled all the beds"'
check "email NOT shown publicly"             '! curl -s $BASE/reviews/ | grep -q "ahmed.k@example.com"'
R=$(loc -X POST $BASE/form/review.php -d "name=Spam&phone=0501234567&rating=5&lang=en&ts=$OLD" --data-urlencode "body=Great deals at www.spam-site.com check it out now")
check "review with a link rejected"          '[[ "$R" == *"error=links"* ]]'
R=$(loc -X POST $BASE/form/review.php -d "name=X&email=not-an-email&rating=5&lang=en&ts=$OLD" --data-urlencode "body=This is a long enough review body text")
check "bad email rejected"                   '[[ "$R" == *"error=email"* ]]'
R=$(loc -X POST $BASE/form/review.php -d "name=Bot&phone=0501234567&rating=5&lang=en&company-website=x&ts=$OLD" --data-urlencode "body=bot text that is long enough to pass")
check "honeypot silently dropped"            '! curl -s $BASE/reviews/ | grep -q "bot text"'
R=$(loc -X POST $BASE/form/review.php --data-urlencode "name=<script>alert(1)</script>" -d "phone=0507654321&rating=4&lang=en&ts=$OLD" --data-urlencode "body=Checking that scripts are escaped properly here.")
check "review without email accepted"        '[[ "$R" == *"/reviews/?thanks=1"* ]]'
R=$(loc -X POST $BASE/form/review.php --data-urlencode "name=Ahmed Khan" -d "rating=5&lang=en&ts=$OLD" --data-urlencode "body=Second review from the same person, which must also be accepted.")
check "same person can review again"         '[[ "$R" == *"/reviews/?thanks=1"* ]]'
check "script tag escaped, not executed"     'curl -s $BASE/reviews/ | grep -q "&lt;script&gt;alert(1)&lt;/script&gt;"'
# Arabic goes through UTF-8 files: Windows mangles non-ASCII command-line arguments into "????"
printf '%s' "أحمد" > "$SCR/e2e-ar-name.txt"
printf '%s' "فريق ممتاز ومحترف جداً في التغليف والنقل" > "$SCR/e2e-ar-body.txt"
R=$(loc -X POST $BASE/form/review.php --data-urlencode "name@$SCR/e2e-ar-name.txt" -d "phone=0509998887&rating=5&lang=ar&ts=$OLD" --data-urlencode "body@$SCR/e2e-ar-body.txt")
check "Arabic review -> Arabic thanks page"  '[[ "$R" == *"/ar/reviews/?thanks=1"* ]]'

echo "Admin: setup and login"
check "admin shows first-time setup"         'curl -s -c $JAR -b $JAR $BASE/admin/ | grep -q "Create your admin account"'
TOKEN=$(curl -s -c $JAR -b $JAR $BASE/admin/ | grep -o 'name="csrf" value="[a-f0-9]*"' | head -1 | sed 's/.*value="//;s/"//')
curl -s -o /dev/null -c $JAR -b $JAR -X POST $BASE/admin/ -d "action=setup&csrf=$TOKEN&setup_key=wrong&username=owner&password=correct-horse-1&password2=correct-horse-1"
check "wrong setup key refused"              'curl -s -c $JAR -b $JAR $BASE/admin/ | grep -q "Create your admin account"'
TOKEN=$(curl -s -c $JAR -b $JAR $BASE/admin/ | grep -o 'name="csrf" value="[a-f0-9]*"' | head -1 | sed 's/.*value="//;s/"//')
curl -s -o /dev/null -c $JAR -b $JAR -X POST $BASE/admin/ -d "action=setup&csrf=$TOKEN&setup_key=$SETUP_KEY&username=owner&password=correct-horse-1&password2=correct-horse-1"
check "account created -> login form"        'curl -s -c $JAR -b $JAR $BASE/admin/ | grep -q "Admin login"'
check "POST without CSRF token -> 403"       '[ "$(code -X POST $BASE/admin/ -d action=login -d username=owner -d password=x)" = 403 ]'
TOKEN=$(curl -s -c $JAR -b $JAR $BASE/admin/ | grep -o 'name="csrf" value="[a-f0-9]*"' | head -1 | sed 's/.*value="//;s/"//')
curl -s -o /dev/null -c $JAR -b $JAR -X POST $BASE/admin/ -d "action=login&csrf=$TOKEN&username=owner&password=wrong-password"
check "wrong password refused"               'curl -s -c $JAR -b $JAR $BASE/admin/ | grep -q "Wrong username or password"'
TOKEN=$(curl -s -c $JAR -b $JAR $BASE/admin/ | grep -o 'name="csrf" value="[a-f0-9]*"' | head -1 | sed 's/.*value="//;s/"//')
curl -s -o /dev/null -c $JAR -b $JAR -X POST $BASE/admin/ -d "action=login&csrf=$TOKEN&username=owner&password=correct-horse-1"
check "correct login -> dashboard"           'curl -s -c $JAR -b $JAR "$BASE/admin/?view=projects" | grep -q "New project"'
check "admin sees reviewer email"            'curl -s -c $JAR -b $JAR "$BASE/admin/?view=reviews" | grep -q "ahmed.k@example.com"'

echo "Admin: projects and uploads"
T2=$(curl -s -c $JAR -b $JAR "$BASE/admin/?view=project" | grep -o 'name="csrf" value="[a-f0-9]*"' | head -1 | sed 's/.*value="//;s/"//')
PR=$(loc -c $JAR -b $JAR -X POST $BASE/admin/ -d "action=project_save&csrf=$T2&id=0&status=published" --data-urlencode "title_en=Villa move in Arabian Ranches" --data-urlencode "location=Arabian Ranches" --data-urlencode "property_type=4-bedroom villa" --data-urlencode "description_en=Packed, moved and reassembled in one day.")
PID=$(echo "$PR" | grep -o 'id=[0-9]*' | cut -d= -f2)
check "project created (id $PID)"            '[ -n "$PID" ]'
curl -s -o /dev/null -c $JAR -b $JAR -X POST $BASE/admin/ -F "action=media_upload" -F "csrf=$T2" -F "id=$PID" -F "media[]=@$IMG" -F "media[]=@$VID"
check "image converted to webp + thumb"      'ls "$ROOT"/dist/uploads/projects/$PID/*-thumb.webp >/dev/null 2>&1'
check "video stored"                         'ls "$ROOT"/dist/uploads/projects/$PID/*.mp4 >/dev/null 2>&1'
printf '<?php echo "pwned";' > "$SCR/evil.php.jpg"
curl -s -o /dev/null -c $JAR -b $JAR -X POST $BASE/admin/ -F "action=media_upload" -F "csrf=$T2" -F "id=$PID" -F "media[]=@$SCR/evil.php.jpg"
check "disguised PHP upload refused"         '! ls "$ROOT"/dist/uploads/projects/$PID/ | grep -qi "php"; [ $(ls "$ROOT"/dist/uploads/projects/$PID | wc -l) -eq 3 ]'
check "projects page shows the project"      'curl -s $BASE/projects/ | grep -q "Villa move in Arabian Ranches"'
check "projects page shows image + video"    'curl -s $BASE/projects/ | grep -q "thumb.webp" && curl -s $BASE/projects/ | grep -q "<video"'

echo "Admin: moderation"
# the newest review is the Arabic one: hide it and make sure it leaves the public page
check "Arabic review stored intact + public" 'curl -s $BASE/ar/reviews/ | grep -qF -f "$SCR/e2e-ar-body.txt"'
RID=$(curl -s -c $JAR -b $JAR "$BASE/admin/?view=reviews" | grep -o 'name="review_id" value="[0-9]*"' | head -1 | grep -o '[0-9]*')
curl -s -o /dev/null -c $JAR -b $JAR -X POST $BASE/admin/ -d "action=review_status&csrf=$T2&review_id=$RID&status=hidden"
check "hidden review disappears from site"   '! curl -s $BASE/ar/reviews/ | grep -qF -f "$SCR/e2e-ar-body.txt"'
check "other reviews still visible"          'curl -s $BASE/reviews/ | grep -q "reassembled all the beds"'
check "logged-out visitor can't see admin"   'curl -s "$BASE/admin/?view=leads" | grep -q "Admin login"'

echo "Quote form (shared database)"
R=$(loc -X POST $BASE/form/quote.php -d "name=Sara&phone=0551112233&lang=en&ts=$OLD")
check "quote stored -> thank-you redirect"   '[[ "$R" == *"/get-a-quote/thank-you/"* ]]'
check "lead visible in admin"                'curl -s -c $JAR -b $JAR "$BASE/admin/?view=leads" | grep -q "0551112233"'

rm -rf "$ROOT/dist/uploads/projects"
echo
echo "$pass passed, $fail failed"
[ "$fail" -eq 0 ]
