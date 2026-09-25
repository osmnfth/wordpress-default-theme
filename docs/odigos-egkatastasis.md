# Οδηγός εγκατάστασης — Ιστότοπος Κοσμητείας

Για τον **τεχνικό υπεύθυνο** που θα στήσει τον ιστότοπο μιας Κοσμητείας σε
πραγματικό server (φιλοξενία του Ιδρύματος, VM, NAS). Προϋποθέτει βασική
εξοικείωση με WordPress ή με τη γραμμή εντολών Linux.

Για τη χρήση του site μετά την εγκατάσταση δείτε το
[Εγχειρίδιο χρήσης](egxeiridio-xrisis.md)· για τον κώδικα, την
[Τεχνική τεκμηρίωση](texniki-tekmiriosi.md).

---

## 1. Ποιον τρόπο να διαλέξω

| | **Α. Σε υπάρχον WordPress** | **Β. Με Docker σε δικό σας server** |
|---|---|---|
| Πότε | Το Ίδρυμα/το Κέντρο Δικτύου σάς δίνει ήδη WordPress | Έχετε δικό σας Linux server, VM ή NAS με Docker |
| Τι ανεβάζετε | Δύο αρχεία `.zip` από τη διαχείριση | Όλο το repository |
| Γραμμή εντολών | Όχι | Ναι (SSH) |
| Ενημερώσεις WordPress, backup, HTTPS | Τα φροντίζει ο πάροχος | Τα φροντίζετε εσείς |

**Αν υπάρχει η επιλογή Α, προτιμήστε την.** Ο τρόπος Β σας δίνει πλήρη έλεγχο,
αλλά και την ευθύνη για ασφάλεια και αντίγραφα.

Και στους δύο τρόπους ο ιστότοπος αποτελείται από **δύο κομμάτια που πρέπει να
είναι και τα δύο ενεργά**: το πρόσθετο **Κοσμητεία Core** (το περιεχόμενο) και
το θέμα **Κοσμητεία** (η εμφάνιση).

---

## 2. Τρόπος Α — Σε υπάρχον WordPress

### Απαιτήσεις

- WordPress **6.6+**, PHP **8.0+**
- Εγκατεστημένο το θέμα **Twenty Twenty-Five** (έρχεται με κάθε νέο WordPress)
- Λογαριασμός **Διαχειριστή**

### Βήματα

1. Φτιάξτε τα αρχεία εγκατάστασης (σε οποιονδήποτε υπολογιστή με Python 3):
   ```bash
   python tools/build-zips.py
   ```
   Παράγονται στον φάκελο `dist/` τα `kosmiteia-core-<έκδοση>.zip` και `kosmiteia-<έκδοση>.zip`.
2. **Πρόσθετα → Προσθήκη → Ανέβασμα** → `kosmiteia-core-….zip` → **Ενεργοποίηση**.
3. **Εμφάνιση → Θέματα → Προσθήκη → Ανέβασμα** → `kosmiteia-….zip` → **Ενεργοποίηση**.
   Η σειρά μετράει: πρώτα το πρόσθετο, μετά το θέμα.
4. **Ρυθμίσεις → Μόνιμοι σύνδεσμοι** → «Όνομα άρθρου» → Αποθήκευση.
5. **Κοσμητεία → Ρυθμίσεις**: όνομα, στοιχεία επικοινωνίας, χάρτης, κοινωνικά δίκτυα.
6. Προαιρετικά, **Κοσμητεία → Εργαλεία**:
   - *Δημιουργία αρχικού περιεχομένου*: δοκιμαστικές σελίδες, Τμήματα και μενού ως αφετηρία.
   - *Εισαγωγή ανακοινώσεων από παλιό ιστότοπο*: μέσω RSS, μαζί με τα PDF.

Συνεχίστε στην [ενότητα 5](#5-πριν-το-άνοιγμα-στο-κοινό).

---

## 3. Τρόπος Β — Με Docker σε δικό σας server

Το `docker-compose.yml` στήνει WordPress (PHP 8.3 / Apache), MariaDB 11 και ένα
container `provision` που εγκαθιστά και ρυθμίζει τα πάντα αυτόματα.

### Απαιτήσεις

- Docker με **Docker Compose v2** (η εντολή είναι `docker compose`, όχι `docker-compose`)
- Git
- Πρόσβαση στο internet κατά το πρώτο στήσιμο (ελληνικά, Twenty Twenty-Five, FileBird)
- Μια ελεύθερη θύρα (προεπιλογή **8090**)

> **Synology NAS:** δουλέψτε πάντα μέσα από την πραγματική διαδρομή
> `/volume1/...` και όχι από το `~` (που δείχνει στο `/var/services/homes/...`).
> Το Docker του Synology δεν μπορεί να κάνει mount φακέλους μέσα από αυτό το
> symlink. Βλ. [Αντιμετώπιση προβλημάτων](#8-αντιμετώπιση-προβλημάτων).

### 3.1 Λήψη του κώδικα

```bash
cd /volume1/docker        # ή όπου κρατάτε τα projects σας
git clone https://github.com/osmnfth/wordpress-default-theme.git kosmiteia
cd kosmiteia
```

Ο φάκελος πρέπει να **μείνει εκεί**: το θέμα και το πρόσθετο δεν αντιγράφονται
μέσα στο container, διαβάζονται απευθείας από αυτόν τον φάκελο.

### 3.2 Δικαιώματα αρχείων

Ο Apache και το `provision` τρέχουν ως χρήστης `www-data` (uid 33) και πρέπει
να μπορούν να **διαβάσουν** τα αρχεία του project:

```bash
chmod -R a+rX .
```

Χωρίς αυτό, το στήσιμο αποτυγχάνει με `Permission denied` και το site δείχνει
το προεπιλεγμένο θέμα του WordPress.

### 3.3 Ρυθμίσεις (`.env`)

Δημιουργήστε αρχείο `.env` δίπλα στο `docker-compose.yml`:

```ini
KOSMITEIA_URL=https://kosmiteia.example.gr
KOSMITEIA_PORT=8090
KOSMITEIA_ADMIN_USER=admin
KOSMITEIA_ADMIN_PASSWORD=<ισχυρός κωδικός>
KOSMITEIA_ADMIN_EMAIL=webmaster@example.gr
```

| Μεταβλητή | Τι είναι |
|---|---|
| `KOSMITEIA_URL` | Η διεύθυνση **ακριβώς όπως θα την πληκτρολογούν οι επισκέπτες**. Χωρίς `/` στο τέλος. Αν δεν έχετε ακόμα domain: `http://<IP>:8090`. |
| `KOSMITEIA_PORT` | Η θύρα του server όπου ακούει το WordPress |
| `KOSMITEIA_ADMIN_*` | Ο πρώτος διαχειριστής. Ισχύει **μόνο στο πρώτο στήσιμο**· μετά αλλάζει από το wp-admin. |

> **Αν λείπει το `.env`**, ο διαχειριστής δημιουργείται ως `admin` / `admin`.
> Σε server προσβάσιμο από το internet αυτό είναι επικίνδυνο.

> Το `.env` **δεν μπαίνει στο git**. Φτιάξτε το με επεξεργαστή κειμένου ή με
> `nano .env` πάνω στον server. Μην το γράφετε με `echo … >>` από το PowerShell
> των Windows: το PowerShell 5.1 το γράφει σε UTF-16 και το Docker δεν μπορεί
> να το διαβάσει.

### 3.4 Εκκίνηση

```bash
docker compose up -d
docker compose logs -f provision
```

Περιμένετε μέχρι να δείτε:

```
[kosmiteia] Ready: https://kosmiteia.example.gr
```

Μετά το `Ready` το container `provision` τερματίζει. Αυτό είναι φυσιολογικό.
Αν σταματήσει νωρίτερα με σφάλμα, δείτε την
[ενότητα 8](#8-αντιμετώπιση-προβλημάτων).

### 3.5 HTTPS

Το container δίνει μόνο HTTP. Για HTTPS βάλτε μπροστά έναν **reverse proxy**
που κρατά το πιστοποιητικό και προωθεί στο `http://localhost:8090`:

- **Synology:** Control Panel → Login Portal → Advanced → **Reverse Proxy**,
  και πιστοποιητικό Let's Encrypt από Control Panel → Security → Certificate.
- **Linux server:** Caddy, nginx ή Traefik, όποιο χρησιμοποιεί ήδη το Ίδρυμα.

Ο proxy πρέπει να στέλνει την κεφαλίδα `X-Forwarded-Proto: https`. Την
αναγνωρίζει αυτόματα η εικόνα του WordPress. Το `KOSMITEIA_URL` πρέπει να
ξεκινά με `https://`. Αφού ενεργοποιηθεί ο proxy, κλείστε τη θύρα 8090 στο
firewall, ώστε το site να είναι προσβάσιμο μόνο μέσω HTTPS.

### 3.6 Τι στήθηκε

- Ελληνικό WordPress, Twenty Twenty-Five, ενεργά θέμα **Κοσμητεία**, πρόσθετο
  **Κοσμητεία Core** και **FileBird**
- Μόνιμοι σύνδεσμοι `/%postname%/`
- Δοκιμαστικό περιεχόμενο και μενού, που αντικαθιστάτε με το δικό σας

---

## 4. Μεταφορά περιεχομένου από τοπικό σε server

Αν έχετε ήδη δουλέψει το site τοπικά (π.χ. με `docker compose up` στον υπολογιστή
σας), το περιεχόμενο **δεν** βρίσκεται στο git. Βρίσκεται σε δύο σημεία, και
χρειάζονται **και τα δύο**:

| | Τι περιέχει | Πού |
|---|---|---|
| Βάση δεδομένων | σελίδες, ανακοινώσεις, μενού, ρυθμίσεις, χρήστες, εγγραφές Βιβλιοθήκης | volume `db-data` |
| Αρχεία πολυμέσων | φωτογραφίες, PDF, έγγραφα | `wp-content/uploads` στο volume `wp-data` |

Ο server πρέπει να τρέχει **την ίδια έκδοση κώδικα** με τον υπολογιστή σας.
Κάντε πρώτα `git push` τις αλλαγές σας και `git pull` στον server.

### 4.1 Εξαγωγή (στον υπολογιστή σας, μέσα στον φάκελο του project)

```bash
docker compose run --rm --entrypoint wp provision db export /var/www/html/kosmiteia.sql
docker compose cp wordpress:/var/www/html/kosmiteia.sql .
docker compose cp wordpress:/var/www/html/wp-content/uploads ./uploads
docker compose exec wordpress rm /var/www/html/kosmiteia.sql
```

Τα `kosmiteia.sql` και `uploads/` εξαιρούνται από το git (`.gitignore`). Το dump
περιέχει λογαριασμούς χρηστών: **μην το ανεβάσετε στο GitHub** και μην το
στείλετε με email.

### 4.2 Αποστολή στον server

```bash
scp -O -r kosmiteia.sql uploads χρήστης@<server>:/διαδρομή/του/project/
```

Το `-O` χρειάζεται όταν ο server δεν έχει ενεργό SFTP, όπως συμβαίνει στα
Synology από προεπιλογή. Εναλλακτικά, ανεβάστε τα από το File Station.

### 4.3 Εισαγωγή (στον server, μέσα στον φάκελο του project)

```bash
docker compose cp uploads/. wordpress:/var/www/html/wp-content/uploads/
docker compose exec wordpress chown -R www-data:www-data /var/www/html/wp-content/uploads
docker compose cp kosmiteia.sql wordpress:/var/www/html/
docker compose run --rm --entrypoint wp provision db import /var/www/html/kosmiteia.sql
docker compose run --rm --entrypoint wp provision search-replace 'http://localhost:8090' 'https://kosmiteia.example.gr' --all-tables
docker compose run --rm --entrypoint wp provision cache flush
docker compose exec wordpress rm /var/www/html/kosmiteia.sql
rm kosmiteia.sql
```

- Το `db import` **αντικαθιστά όλη τη βάση του server**, μαζί με τους χρήστες.
  Από εκεί και πέρα συνδέεστε με τους κωδικούς του υπολογιστή σας.
- Το `search-replace` χρειάζεται επειδή οι διευθύνσεις εικόνων και συνδέσμων
  είναι αποθηκευμένες στη βάση με την παλιά διεύθυνση. Χωρίς αυτό, οι εικόνες
  θα φαίνονται σπασμένες σε κάθε επισκέπτη. Η δεύτερη διεύθυνση πρέπει να είναι
  ίδια με το `KOSMITEIA_URL`.

---

## 5. Πριν το άνοιγμα στο κοινό

- [ ] Ο κωδικός του διαχειριστή είναι ισχυρός και **διαφορετικός** από αυτόν της τοπικής εγκατάστασης
- [ ] Υπάρχουν λογαριασμοί *Συντάκτης Ανακοινώσεων* για τη γραμματεία, ώστε να μη μοιράζεται ο λογαριασμός διαχειριστή
- [ ] Το site ανοίγει με **HTTPS** και η θύρα 8090 δεν είναι ανοιχτή προς το internet
- [ ] **Κοσμητεία → Ρυθμίσεις**: σωστά στοιχεία επικοινωνίας, χάρτης και κοινωνικά δίκτυα
- [ ] Το δοκιμαστικό περιεχόμενο έχει αντικατασταθεί ή διαγραφεί, και τα Τμήματα και οι ανακοινώσεις είναι τα πραγματικά
- [ ] Το λογότυπο και οι φωτογραφίες είναι της δικής σας Κοσμητείας
- [ ] Η αγγλική έκδοση (`?lang=en`) έχει ελεγχθεί
- [ ] Στις **Ρυθμίσεις → Ανάγνωση** η επιλογή «Αποθάρρυνση μηχανών αναζήτησης» είναι **απενεργοποιημένη**
- [ ] Τα αυτόματα αντίγραφα ασφαλείας ([ενότητα 7](#7-αντίγραφα-ασφαλείας)) τρέχουν και έχει γίνει μία δοκιμαστική επαναφορά

---

## 6. Ενημερώσεις

### Τρόπος Α

Φτιάξτε νέα `.zip` με `python tools/build-zips.py` και ανεβάστε τα όπως στην
εγκατάσταση. Το WordPress ρωτά αν θέλετε **αντικατάσταση** της υπάρχουσας
έκδοσης. Απαντήστε ναι. Το περιεχόμενο δεν επηρεάζεται.

### Τρόπος Β

```bash
cd /διαδρομή/του/project
git pull
chmod -R a+rX .
docker compose up -d
```

Το θέμα και το πρόσθετο ενημερώνονται αμέσως. Το `provision` ξανατρέχει, αλλά
**δεν αντικαθιστά** υπάρχον περιεχόμενο. Το ίδιο το WordPress ενημερώνεται από
το wp-admin, όπως συνήθως.

**Πάντα κρατήστε αντίγραφο ασφαλείας πριν από μια ενημέρωση.**

---

## 7. Αντίγραφα ασφαλείας

Στον τρόπο Α τα αναλαμβάνει συνήθως ο πάροχος. Επιβεβαιώστε το μαζί του.

Στον τρόπο Β, από τον φάκελο του project:

```bash
D=$(date +%F)
docker compose run --rm --entrypoint wp provision db export /var/www/html/backup.sql
docker compose cp wordpress:/var/www/html/backup.sql ./backup-$D.sql
docker compose exec wordpress rm /var/www/html/backup.sql
docker compose cp wordpress:/var/www/html/wp-content/uploads ./backup-uploads-$D
```

Βάλτε το σε ένα script και τρέξτε το αυτόματα, π.χ. κάθε βράδυ, με `cron` ή
με το Task Scheduler του Synology. Αντιγράψτε τα αντίγραφα και **εκτός** του
server. Η επαναφορά γίνεται με τα βήματα της [ενότητας 4.3](#43-εισαγωγή-στον-server-μέσα-στον-φάκελο-του-project),
χωρίς το `search-replace` αν η διεύθυνση δεν άλλαξε.

> **Προσοχή:** το `docker compose down -v` **σβήνει τη βάση και όλα τα
> πολυμέσα**. Σε server χρησιμοποιείτε μόνο `docker compose down` (χωρίς `-v`).

---

## 8. Αντιμετώπιση προβλημάτων

Το πρώτο βήμα είναι σχεδόν πάντα:

```bash
docker compose logs provision
```

| Σύμπτωμα | Αιτία | Λύση |
|---|---|---|
| Φαίνεται το προεπιλεγμένο θέμα του WordPress, όχι της Κοσμητείας | Το `provision` σταμάτησε πριν ενεργοποιήσει το θέμα | Δείτε τα logs του `provision`· συνήθως είναι μία από τις επόμενες γραμμές |
| `can't open '/provision/provision.sh': Permission denied` | Τα αρχεία δεν διαβάζονται από τον uid 33 | `chmod -R a+rX .` και `docker compose up -d --force-recreate provision`. Σε Synology ελέγξτε και τα ACL του φακέλου από το File Station. |
| `Bind mount failed: '/var/services/homes/…' does not exist` | Synology: μπήκατε στον φάκελο μέσω `~` | `cd /volume1/homes/<χρήστης>/…` και ξανά `docker compose up -d` |
| `Bind mount failed: '…/plugins/kosmiteia-core' does not exist` (με σωστή διαδρομή) | Ο server έχει παλιότερη έκδοση ή λάθος branch | `git status`, `git pull`, και έλεγχος με `ls plugins/kosmiteia-core` |
| Οι εικόνες φαίνονται σπασμένες, ή οι σύνδεσμοι οδηγούν στο `localhost` | Η βάση έχει ακόμα την παλιά διεύθυνση | `search-replace` της [ενότητας 4.3](#43-εισαγωγή-στον-server-μέσα-στον-φάκελο-του-project) και σωστό `KOSMITEIA_URL` στο `.env`, μετά `docker compose up -d` |
| Η σύνδεση στο wp-admin γυρίζει συνέχεια στη σελίδα εισόδου, ή HTTPS και HTTP εναλλάσσονται | Το `KOSMITEIA_URL` δεν ταιριάζει με τη διεύθυνση του browser | Διορθώστε το `.env` (http/https, θύρα) και `docker compose up -d` |
| `failed to read .env: unexpected character "\x00"` | Το `.env` γράφτηκε σε UTF-16, π.χ. από PowerShell | Ξαναγράψτε το με κανονικό επεξεργαστή κειμένου σε UTF-8 |
| `scp`: `subsystem request failed on channel 0` | Ο server δεν έχει ενεργό SFTP | `scp -O …` ή ενεργοποίηση SFTP (Synology: Control Panel → File Services → FTP) |
| `docker compose run --rm provision wp …` δεν κάνει αυτό που ζητήσατε | Το `provision` ξανατρέχει το script στησίματος και αγνοεί την εντολή | Προσθέστε `--entrypoint wp`: `docker compose run --rm --entrypoint wp provision …` |
| Στη διαχείριση: ειδοποίηση ότι λείπει το πρόσθετο | Το **Κοσμητεία Core** δεν είναι ενεργό | **Πρόσθετα** → Ενεργοποίηση |

Για οποιαδήποτε εντολή WP-CLI:

```bash
docker compose run --rm --entrypoint wp provision <εντολή>
# π.χ.
docker compose run --rm --entrypoint wp provision theme list
docker compose run --rm --entrypoint wp provision kosmiteia info
docker compose run --rm --entrypoint wp provision user update admin --user_pass='<νέος κωδικός>'
```
