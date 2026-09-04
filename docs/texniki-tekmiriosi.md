# Τεχνική τεκμηρίωση — Ιστότοπος Κοσμητείας

Για όποιον θα συντηρήσει ή θα επεκτείνει τον κώδικα. Περιλαμβάνει την
αρχιτεκτονική, ένα πλήρες παράδειγμα προσθήκης πεδίου στις Ανακοινώσεις και τη
στρατηγική ενημερώσεων.

---

## 1. Στοίβα τεχνολογιών

| Επίπεδο | Τι χρησιμοποιείται | Γιατί |
|---|---|---|
| CMS | **WordPress** (block theme / Full Site Editing) | ο ιστότοπος επεξεργάζεται εξ ολοκλήρου από το UI, χωρίς κώδικα |
| Θέμα | **child του Twenty Twenty-Five**, `theme.json` v3 | παίρνουμε δωρεάν τη συντήρηση του γονικού· ορίζουμε μόνο παλέτα, τυπογραφία, διατάξεις |
| Λειτουργικότητα | **πρόσθετο «Κοσμητεία Core»** (PHP 8.0+) | το περιεχόμενο επιβιώνει σε αλλαγή θέματος |
| Δεδομένα | Custom Post Types, taxonomies, `register_post_meta`, options | τα πάντα με πυρήνα WordPress — καμία εξωτερική βάση |
| Σύνδεση δεδομένων ↔ εμφάνισης | **Block Bindings** (`core/post-meta`, `kosmiteia/option`) | πεδία και ρυθμίσεις μπαίνουν σε μπλοκ χωρίς shortcodes |
| Μπλοκ | `block.json` + **vanilla JavaScript** (χωρίς JSX) | **κανένα build step**: ούτε npm, ούτε webpack |
| Χάρτης | **Leaflet 1.9.4 τοπικά** + OpenStreetMap tiles | χωρίς CDN, χωρίς cookies τρίτων (GDPR) |
| Πολυμέσα | WordPress Media Library (+ FileBird για φακέλους) | — |
| Μεταφράσεις | δικό μας `build-translations.py` (μόνο stdlib) | παράγει `.pot/.po/.mo` χωρίς gettext εργαλεία |
| Ανάπτυξη | **Docker Compose**: WordPress (PHP 8.3/Apache) + MariaDB 11 + WP-CLI | ίδιο περιβάλλον για όλους, στήσιμο με μία εντολή |
| Αυτοματισμοί | **WP-CLI** (`wp kosmiteia …`) | seed, εισαγωγή ανακοινώσεων, αναφορές |

Τι **δεν** χρησιμοποιείται σκόπιμα: page builder, ACF, jQuery plugins, εξωτερικά
CDN, npm/Node build. Όλα με πυρήνα WordPress, ώστε το site να είναι
συντηρήσιμο από οποιονδήποτε γνωρίζει WordPress.

---

## 2. Αρχιτεκτονική: ποιος κάνει τι

```
┌───────────────────────────┐        ┌────────────────────────────────┐
│  ΘΕΜΑ  kosmiteia/         │        │  ΠΡΟΣΘΕΤΟ  kosmiteia-core/     │
│  (εμφάνιση)               │        │  (λειτουργικότητα)             │
│                           │        │                                │
│  templates/  πρότυπα      │◀──────▶│  τύποι περιεχομένου + πεδία    │
│  parts/      κεφαλίδα...  │ blocks │  ταξινομίες                    │
│  patterns/   έτοιμα μπλοκ │  meta  │  6 custom blocks               │
│  theme.json  σχεδίαση     │ options│  φίλτρα, breadcrumbs, χάρτης   │
│  assets/css  στυλ         │        │  δίγλωσσο, SEO/schema          │
│  inc/setup   assets       │        │  ρυθμίσεις, ρόλοι, εργαλεία    │
└───────────────────────────┘        └────────────────────────────────┘
```

**Κανόνας:** ό,τι αφορά *πώς φαίνεται* → θέμα. Ό,τι αφορά *τι υπάρχει* →
πρόσθετο. Αν αλλάξει το θέμα, το site χάνει μόνο την εμφάνιση.

### Πού ζει το κάθε δεδομένο

| Δεδομένο | Αποθήκευση | Πώς φτάνει στη σελίδα |
|---|---|---|
| Ανακοινώσεις, Σχολές, Εκδηλώσεις… | πίνακας `posts` (CPT) | Query Loop στα templates |
| Κατηγορίες, Σχολή (φίλτρο) | taxonomies | `post-terms`, φίλτρα αρχείου |
| Προθεσμία, συνημμένο, τόπος… | `postmeta` (`register_post_meta`) | Block Bindings `core/post-meta` |
| Τηλέφωνο, διεύθυνση, χάρτης, social | option `kosmiteia_settings` | Block Bindings `kosmiteia/option` |
| Κεφαλίδα, υποσέλιδο, ενότητες αρχικής | αρχεία `parts/` **ή** βάση αν επεξεργαστούν | template parts |
| Λογότυπο, μενού | theme mods / `wp_navigation` | μπλοκ site-logo / navigation |

---

## 3. Χάρτης αρχείων

```
plugins/kosmiteia-core/
├─ kosmiteia-core.php          επικεφαλίδα, constants, requires, activation
├─ uninstall.php               σβήνει μόνο options + ρόλο
├─ readme.txt
├─ includes/
│  ├─ settings.php             σχήμα ρυθμίσεων, σελίδα, kosmiteia_option()
│  ├─ post-types.php           Σχολές, Ανακοινώσεις, Μεταπτυχιακά + taxonomies + meta
│  ├─ post-types-academic.php  Εκδηλώσεις, Προσωπικό, Έγγραφα
│  ├─ roles.php                ρόλος «Συντάκτης Ανακοινώσεων»
│  ├─ bindings.php             πηγή kosmiteia/option, tokens {{...}}, excerpt
│  ├─ announcements.php        φίλτρα/αναζήτηση αρχείου (pre_get_posts)
│  ├─ multilingual.php         ?lang=en, template parts, hreflang
│  ├─ breadcrumbs.php          διαδρομή πλοήγησης
│  ├─ blocks.php               καταχώριση 6 μπλοκ + assets
│  ├─ gallery.php              πεδίο γκαλερί + lightbox
│  ├─ media.php                μεγέθη/χειρισμός αρχείων
│  ├─ seo.php                  meta tags, Open Graph, JSON-LD schema
│  ├─ demo-content.php         αρχικό περιεχόμενο (admin + CLI)
│  ├─ importer-announcements.php  εισαγωγή από RSS παλιού site
│  ├─ admin.php                σελίδα «Εργαλεία»
│  └─ cli.php                  wp kosmiteia seed | import-announcements | info
├─ blocks/                     slider, language-switcher, announcement-filters,
│                              breadcrumbs, map, gallery  (block.json + index.js)
└─ assets/                     admin.css, slider/map/lightbox/admin-gallery.js,
                               vendor/leaflet, demo/ (εικόνες seed)

kosmiteia/
├─ theme.json                  παλέτα, τυπογραφία, πλάτη, block styles
├─ functions.php + inc/        assets, block styles, pattern categories, dependency notice
├─ templates/                  front-page, page, archive/single ανά CPT
├─ parts/                      header, footer, home-*
├─ patterns/                   7 μοτίβα κατηγορίας «Κοσμητεία»
├─ assets/css/theme.css        όλα τα στυλ (και των μπλοκ)
├─ build-translations.py       παράγει .pot/.po/.mo για θέμα + πρόσθετο
└─ languages/

docker/                        provision.sh (στήσιμο site με WP-CLI)
tools/build-zips.py            παράγει dist/*.zip για εγκατάσταση από τη διαχείριση
```

---

## 4. Βασικοί μηχανισμοί

**Φίλτρα ανακοινώσεων** — `kosmiteia_filter_announcement_archive()` στο
`pre_get_posts`: παίρνει `kosm_q`, `kosm_cat`, `kosm_fac`, `kosm_year` από το URL
και τα εφαρμόζει στο *κύριο* query. Έτσι το Query Loop του template μένει
`inherit: true` και η σελιδοποίηση κρατά αυτόματα τις παραμέτρους.

**Δίγλωσσο** — χωρίς plugin: `?lang=en` → φίλτρο `locale` → φορτώνονται τα
αγγλικά `.mo` και αντικαθίστανται τα template parts με τα `*-en`. Τα αγγλικά
κείμενα των μοτίβων παράγονται από τα ίδια αρχεία με `switch_to_locale()`.

**SEO** — `seo.php`: title/description, Open Graph, canonical και JSON-LD graph
(`CollegeOrUniversity`, `EducationalOrganization`, `NewsArticle`, `Course`,
`Event`, `Person`, `DigitalDocument`, `BreadcrumbList`).

**Δικαιώματα** — οι Ανακοινώσεις έχουν δικά τους capabilities
(`capability_type => kosm_announcement`, `map_meta_cap => true`). Ο ρόλος
`kosmiteia_announcer` παίρνει τα «δικά μου» (edit/publish/delete + published),
ο administrator/editor και τα «others/private». Τα ταξινομικά πεδία δηλώνουν
`assign_terms => edit_kosm_announcements`, ώστε ο συντάκτης να επιλέγει
κατηγορίες χωρίς να τις διαχειρίζεται.

---

## 5. Παράδειγμα: προσθήκη πεδίου στις Ανακοινώσεις

Έστω ότι θέλουμε πεδίο **«Αρμόδια υπηρεσία»** (`kosm_department`).

### Βήμα 1 — Δήλωση του πεδίου (υποχρεωτικό)

`plugins/kosmiteia-core/includes/post-types.php`, στη συνάρτηση
`kosmiteia_register_meta()`:

```php
'kosm_announcement' => array(
    'kosm_deadline'   => __( 'Προθεσμία', 'kosmiteia' ),
    'kosm_file_url'   => __( 'Συνημμένο αρχείο (URL)', 'kosmiteia' ),
    'kosm_department' => __( 'Αρμόδια υπηρεσία', 'kosmiteia' ),   // ← νέο
),
```

Αυτό αρκεί για να: αποθηκεύεται, να περνά από REST (άρα να λειτουργεί ο
editor), να έχει έλεγχο δικαιωμάτων και ετικέτα στα Προσαρμοσμένα πεδία.

### Βήμα 2 — Εμφάνιση στη σελίδα

`kosmiteia/templates/single-kosm_announcement.html`, όπου θέλουμε να φανεί:

```html
<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"core/post-meta",
     "args":{"key":"kosm_department"}}}},"fontSize":"small"} -->
<p class="has-small-font-size">Αρμόδια υπηρεσία</p>
<!-- /wp:paragraph -->
```

Το κείμενο μέσα στο `<p>` είναι μόνο placeholder του editor· στο front-end
αντικαθίσταται από την τιμή του πεδίου. Αν το πεδίο είναι κενό, το μπλοκ δεν
εμφανίζει τίποτα.

Για κουμπί με σύνδεσμο, δέσιμο στο `url`:

```html
<!-- wp:button {"metadata":{"bindings":{"url":{"source":"core/post-meta",
     "args":{"key":"kosm_apply_url"}}}}} -->
```

### Βήμα 3 — Συμπλήρωση από τον χρήστη

Χωρίς τίποτα άλλο, το πεδίο εμφανίζεται στον πίνακα **Προσαρμοσμένα πεδία** και
είναι επεξεργάσιμο απευθείας πάνω στο συνδεδεμένο μπλοκ.

### Βήμα 4 — Προαιρετικά

- **Στήλη στη λίστα**: `manage_posts_columns` + `manage_posts_custom_column`
  (δείτε `post-types-academic.php` για έτοιμο παράδειγμα).
- **Φίλτρο/αναζήτηση**: `meta_query` μέσα στο `kosmiteia_filter_announcement_archive()`.
- **Εισαγωγή από παλιό site**: συμπλήρωση στο `importer-announcements.php`
  (εκεί που γράφεται το `kosm_file_url`).
- **Schema**: πρόσθετο πεδίο στο `seo.php`.
- **Μετάφραση**: προσθήκη του string στο λεξικό `EN` του `build-translations.py`
  και `python kosmiteia/build-translations.py`.

### Βήμα 5 — Έκδοση

Ανεβάστε το `Version:` και το `KOSMITEIA_CORE_VERSION` στο `kosmiteia-core.php`
(π.χ. 1.0.0 → 1.1.0). Ενεργοποιεί τους ελέγχους έκδοσης (rewrites, ρόλοι) στο
επόμενο φόρτωμα.

> **Δεν χρειάζεται migration** για νέο πεδίο: το `postmeta` δεν έχει σχήμα.
> Migration χρειάζεται μόνο αν **μετονομάσετε** ή μετασχηματίσετε υπάρχον
> κλειδί (βλ. §7).

---

## 6. Άλλες συνηθισμένες επεκτάσεις

| Θέλω | Πού |
|---|---|
| νέο τύπο περιεχομένου | `post-types-academic.php` (αντιγραφή ενός block `register_post_type`) + `archive-*.html` / `single-*.html` στο θέμα |
| νέα ρύθμιση ιστότοπου | `settings.php` → `kosmiteia_settings_schema()`· διαβάζεται με `kosmiteia_option()` ή binding `kosmiteia/option` |
| νέο μπλοκ | φάκελος στο `blocks/` με `block.json` + `index.js` (vanilla JS) + `register_block_type()` στο `blocks.php` |
| νέο μοτίβο (pattern) | αρχείο PHP στο `kosmiteia/patterns/` με επικεφαλίδα `Title/Slug/Categories` |
| αλλαγή χρωμάτων/τυπογραφίας | `kosmiteia/theme.json` (ή Εμφάνιση → Επεξεργαστής → Στυλ) |
| νέο δικαίωμα/ρόλο | `roles.php` και αύξηση έκδοσης (τρέχει ο συγχρονισμός caps) |

---

## 7. Στρατηγική ενημερώσεων

### 7.1 Πού πάει η κάθε αλλαγή

| Αλλαγή | Αρχείο/σημείο | Χρειάζεται deploy; |
|---|---|---|
| Κείμενο ανακοίνωσης, τηλέφωνο, λογότυπο | βάση (wp-admin) | όχι |
| Χρώματα, διάταξη ενότητας | θέμα ή Site Editor | ναι αν αλλάξει αρχείο |
| Νέο πεδίο/τύπος/δικαίωμα | πρόσθετο | ναι |

### 7.2 Εκδόσεις (semantic versioning)

`1.<minor>.<patch>` — **patch** για διορθώσεις, **minor** για νέα λειτουργία,
**major** για αλλαγές που σπάνε συμβατότητα (π.χ. μετονομασία meta key).
Ενημερώνονται μαζί: `Version:` στην επικεφαλίδα και `KOSMITEIA_CORE_VERSION`.

Τέσσερα options λειτουργούν ως «σημαίες» μετάβασης και ενεργοποιούν αυτόματα
ό,τι χρειάζεται όταν αλλάξει η έκδοση:

| Option | Τι ελέγχει |
|---|---|
| `kosmiteia_core_version` | γενική έκδοση εγκατάστασης |
| `kosmiteia_rewrites_version` | ανανέωση permalinks |
| `kosmiteia_roles_version` | συγχρονισμός capabilities |
| `kosmiteia_demo_version` | αν έχει τρέξει το αρχικό περιεχόμενο |

Πρότυπο migration (μπαίνει στο πρόσθετο και τρέχει μία φορά):

```php
function kosmiteia_migrate_1_1_0() {
    if ( version_compare( get_option( 'kosmiteia_core_version', '0' ), '1.1.0', '>=' ) ) {
        return;
    }

    // π.χ. μετονομασία meta key
    global $wpdb;
    $wpdb->update( $wpdb->postmeta, array( 'meta_key' => 'kosm_new' ), array( 'meta_key' => 'kosm_old' ) );

    update_option( 'kosmiteia_core_version', '1.1.0' );
}
add_action( 'init', 'kosmiteia_migrate_1_1_0', 5 );
```

### 7.3 Ροή δουλειάς

1. **Branch** στο git (`main` = έκδοση με τα πάντα στο θέμα, `plugins` = έκδοση
   με πρόσθετο· δουλεύουμε στο δεύτερο).
2. **Τοπική δοκιμή** στο Docker: `docker compose up -d`, αλλαγές, έλεγχος.
   Για καθαρό έλεγχο από το μηδέν: `docker compose down -v && docker compose up -d`.
3. **Έλεγχοι πριν το commit**:
   - `php -l` σε ό,τι άλλαξε
   - `python kosmiteia/build-translations.py` → `missing: 0`
   - `wp kosmiteia info` (μετρήσεις περιεχομένου)
   - άνοιγμα αρχικής, αρχείου ανακοινώσεων, μιας σελίδας, και σε `?lang=en`
   - `wp-content/debug.log` χωρίς νέα PHP notices
4. **Commit** με περιγραφικό μήνυμα.
5. **Πακετάρισμα**: `python tools/build-zips.py` → `dist/*.zip`.
6. **Deploy** στο πραγματικό site (§7.4).

### 7.4 Deploy στο παραγωγικό

**Πριν από κάθε ενημέρωση:**

```bash
wp db export backup-$(date +%F).sql     # βάση
tar czf uploads-$(date +%F).tgz wp-content/uploads   # αρχεία
```

**Ενημέρωση προσθέτου/θέματος** — δύο επιλογές:

- **Μέσω διαχείρισης**: Πρόσθετα → Προσθήκη → Ανέβασμα του νέου
  `kosmiteia-core-x.y.z.zip` → «Αντικατάσταση τρέχοντος». Ίδιο και για το θέμα.
- **Μέσω server**: `git pull` και αντιγραφή φακέλων, ή `wp plugin install
  kosmiteia-core-x.y.z.zip --force`.

**Μετά:**

```bash
wp cache flush
wp rewrite flush            # αν προστέθηκε τύπος περιεχομένου
wp kosmiteia info           # γρήγορος έλεγχος
```

**Rollback**: κρατήστε το προηγούμενο zip. Επαναφορά με το ίδιο
`--force` και, αν είχε τρέξει migration, επαναφορά της βάσης από το backup.

### 7.5 Ενημερώσεις WordPress / PHP

- **Δευτερεύουσες εκδόσεις WordPress** (x.y.**z**): αυτόματες, ασφαλείς.
- **Κύριες** (x.**y**): πρώτα στο Docker. Τα block themes είναι ευαίσθητα σε
  αλλαγές markup — μετά την αναβάθμιση ανοίξτε τον Επεξεργαστή και ελέγξτε ότι
  δεν εμφανίζονται «ειδοποιήσεις επικύρωσης μπλοκ».
- **Twenty Twenty-Five (γονικό θέμα)**: ενημερώνεται κανονικά· τα δικά μας
  αρχεία δεν πειράζονται (child theme).
- **PHP**: απαιτείται ≥ 8.0· δοκιμασμένο σε 8.3. Πριν από αναβάθμιση PHP,
  τρέξτε το Docker με την ίδια έκδοση.
- **Πρόσθετα τρίτων** (FileBird, WP Accessibility): ενημερώνονται πρώτα σε
  δοκιμαστικό, ειδικά όσα αγγίζουν τη Βιβλιοθήκη.

### 7.6 Παγίδες που ήδη συναντήσαμε

| Παγίδα | Αντιμετώπιση |
|---|---|
| Τμήμα προτύπου επεξεργασμένο από τον Site Editor «παγώνει» στη βάση και αγνοεί το αρχείο | Επεξεργαστής → Τμήματα προτύπου → **Επαναφορά** |
| Το `wp kosmiteia seed --force` αντικαθιστούσε το πραγματικό λογότυπο | διορθώθηκε: πλέον δεν αγγίζει υπάρχον λογότυπο |
| Μεταφράσεις προσθέτου δεν φορτώνουν | το αρχείο πρέπει να λέγεται `kosmiteia-en_US.mo` (θέμα: `en_US.mo`) |
| «Translation triggered too early» | καμία κλήση `__()` πριν το `init` — για ρυθμίσεις πολύ νωρίς υπάρχει `kosmiteia_option_raw()` |
| Αλλαγή branch «εξαφανίζει» το πρόσθετο | ο φάκελος `plugins/` υπάρχει μόνο στο branch `plugins` |

---

## 8. Τοπικό περιβάλλον — γρήγορη αναφορά

```bash
docker compose up -d                 # στήσιμο/εκκίνηση (http://localhost:8090)
docker compose logs -f provision     # τι έκανε το provisioning
docker compose down                  # σταμάτημα (κρατά τη βάση)
docker compose down -v               # καθαρό ξεκίνημα από το μηδέν

# WP-CLI (σε Git Bash προσθέστε MSYS_NO_PATHCONV=1 μπροστά)
docker compose run --rm --entrypoint wp provision kosmiteia info
docker compose run --rm --entrypoint wp provision kosmiteia seed --force
docker compose run --rm --entrypoint wp provision kosmiteia import-announcements --dry-run
docker compose run --rm --entrypoint wp provision plugin list
```

Παραγωγή αρχείων εγκατάστασης και μεταφράσεων:

```bash
python tools/build-zips.py                  # dist/kosmiteia-core-x.y.z.zip, dist/kosmiteia-x.y.z.zip
python kosmiteia/build-translations.py      # .pot/.po/.mo σε θέμα και πρόσθετο
```

---

## 9. Γνωστά όρια

- Τα στυλ των μπλοκ του προσθέτου ζουν στο `theme.css` του θέματος: με άλλο
  θέμα τα μπλοκ λειτουργούν αλλά χωρίς τη σχεδίαση.
- Η διγλωσσία είναι «ελαφριά» (ένα περιεχόμενο, μεταφρασμένο περίβλημα). Για
  πλήρως χωριστά ελληνικά/αγγλικά άρθρα χρειάζεται Polylang/WPML — ο κώδικας
  ανιχνεύει και τα δύο και υποχωρεί.
- Η εισαγωγή από το παλιό site διαβάζει RSS: αν το παλιό site αλλάξει δομή, ο
  importer χρειάζεται προσαρμογή.
