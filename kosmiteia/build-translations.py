# -*- coding: utf-8 -*-
"""
Δημιουργεί τα αρχεία μετάφρασης του theme:

    languages/kosmiteia.pot   - πρότυπο για μεταφραστές
    languages/en_US.po        - αγγλική μετάφραση (πηγή: ελληνικά)
    languages/en_US.mo        - μεταγλωττισμένη μορφή που διαβάζει το WP

Σημείωση: τα themes φορτώνουν τα αρχεία με όνομα locale (en_US.mo), σε αντίθεση
με τα plugins που χρησιμοποιούν <domain>-<locale>.mo.

Εκτέλεση:  python build-translations.py
"""

import io
import json
import os
import re
import struct
import glob

TEXTDOMAIN = 'kosmiteia'
HERE = os.path.dirname(os.path.abspath(__file__))

# Οι μεταφράσιμες συμβολοσειρές ζουν πλέον σε δύο σημεία: στο theme (patterns,
# templates helpers) και στο πρόσθετο «Κοσμητεία Core» (τύποι περιεχομένου,
# μπλοκ, ρυθμίσεις). Και τα δύο χρησιμοποιούν το ίδιο textdomain, οπότε
# παράγουμε ένα κοινό κατάλογο και τον γράφουμε και στους δύο φακέλους.
PLUGIN = os.path.join(os.path.dirname(HERE), 'plugins', 'kosmiteia-core')

SOURCES = (
    (HERE, ('*.php', 'inc/*.php', 'patterns/*.php')),
    (PLUGIN, ('*.php', 'includes/*.php', 'blocks/*/*.js')),
)


def source_files():
    """Όλα τα αρχεία που περιέχουν μεταφράσιμα strings."""
    files = []
    for base, patterns in SOURCES:
        if not os.path.isdir(base):
            continue
        for pattern in patterns:
            files.extend(sorted(glob.glob(os.path.join(base, pattern))))
    return files

# Ελληνικό πρωτότυπο -> Αγγλική μετάφραση.
EN = {
    u'Εμφάνιση': u'Display',
    u'Ετικέτες γλωσσών': u'Language labels',
    u'Σύντομες (EL / EN)': u'Short (EL / EN)',
    u'Πλήρεις (Ελληνικά / English)': u'Full (Ελληνικά / English)',
    u'Διάταξη': u'Layout',
    u'Δίπλα-δίπλα': u'Side by side',
    u'Αναδυόμενη λίστα': u'Dropdown',
    u'Τίτλος διαφάνειας': u'Slide title',
    u'Σύντομο κείμενο για τη διαφάνεια.': u'A short line of text for this slide.',
    u'Μάθετε περισσότερα': u'Learn more',
    u'Ρυθμίσεις slider': u'Slider settings',
    u'Αυτόματη εναλλαγή': u'Autoplay',
    u'Σταματά αυτόματα όταν ο χρήστης κάνει hover ή focus, και όταν έχει ενεργό το "μειωμένη κίνηση".':
        u'Pauses automatically on hover or focus, and when the visitor prefers reduced motion.',
    u'Διάρκεια διαφάνειας (ms)': u'Slide duration (ms)',
    u'Βέλη πλοήγησης': u'Navigation arrows',
    u'Κουκκίδες': u'Dots',
    u'Εφέ εναλλαγής': u'Transition effect',
    u'Κύλιση': u'Slide',
    u'Σβήσιμο (fade)': u'Fade',
    u'Περιγραφή για αναγνώστες οθόνης': u'Screen reader description',
    u'Προαιρετικό aria-label, π.χ. "Κεντρική παρουσίαση Κοσμητείας".':
        u'Optional aria-label, e.g. "Main Deanery carousel".',
    u'Slider: κάθε μπλοκ παρακάτω είναι μία διαφάνεια. Στο front-end εμφανίζονται εναλλάξ.':
        u'Slider: each block below is one slide. They rotate on the front end.',
    u'Δεν βρέθηκαν γλώσσες προς εμφάνιση.': u'No languages available to display.',
    u'Επιλογή γλώσσας': u'Language selection',

    # Σχολές / Schools.
    u'Σχολές': u'Schools',
    u'Σχολή': u'School',
    u'Προσθήκη νέας': u'Add New',
    u'Προσθήκη νέας Σχολής': u'Add New School',
    u'Επεξεργασία Σχολής': u'Edit School',
    u'Νέα Σχολή': u'New School',
    u'Προβολή Σχολής': u'View School',
    u'Προβολή Σχολών': u'View Schools',
    u'Αναζήτηση Σχολών': u'Search Schools',
    u'Δεν βρέθηκαν Σχολές': u'No schools found',
    u'Δεν βρέθηκαν Σχολές στον κάδο': u'No schools found in Trash',
    u'Όλες οι Σχολές': u'All Schools',
    u'Αρχείο Σχολών': u'School Archive',
    u'Φωτογραφία Σχολής': u'School image',
    u'Ορισμός φωτογραφίας Σχολής': u'Set school image',
    u'Η Σχολή ενημερώθηκε.': u'School updated.',
    u'Η Σχολή δημοσιεύτηκε.': u'School published.',
    u'Οι Σχολές που υπάγονται στην Κοσμητεία.': u'The schools that belong to the Deanery.',
    u'Σύντομη περιγραφή της Σχολής...': u'A short description of the school...',
    u'Τμήματα': u'Departments',

    # Ανακοινώσεις / Announcements.
    u'Ανακοινώσεις': u'Announcements',
    u'Ανακοίνωση': u'Announcement',
    u'Προσθήκη νέας Ανακοίνωσης': u'Add New Announcement',
    u'Επεξεργασία Ανακοίνωσης': u'Edit Announcement',
    u'Νέα Ανακοίνωση': u'New Announcement',
    u'Προβολή Ανακοίνωσης': u'View Announcement',
    u'Προβολή Ανακοινώσεων': u'View Announcements',
    u'Αναζήτηση Ανακοινώσεων': u'Search Announcements',
    u'Δεν βρέθηκαν Ανακοινώσεις': u'No announcements found',
    u'Δεν βρέθηκαν Ανακοινώσεις στον κάδο': u'No announcements found in Trash',
    u'Όλες οι Ανακοινώσεις': u'All Announcements',
    u'Αρχείο Ανακοινώσεων': u'Announcement Archive',
    u'Ανακοινώσεις, νέα και προκηρύξεις της Κοσμητείας.':
        u'Announcements, news and calls from the Deanery.',

    # Μεταπτυχιακά / Programmes.
    u'Μεταπτυχιακά': u'Postgraduate',
    u'Μεταπτυχιακό Πρόγραμμα': u'Postgraduate Programme',
    u'Προσθήκη νέου': u'Add New',
    u'Προσθήκη νέου Προγράμματος': u'Add New Programme',
    u'Επεξεργασία Προγράμματος': u'Edit Programme',
    u'Νέο Πρόγραμμα': u'New Programme',
    u'Προβολή Προγράμματος': u'View Programme',
    u'Προβολή Προγραμμάτων': u'View Programmes',
    u'Αναζήτηση Προγραμμάτων': u'Search Programmes',
    u'Δεν βρέθηκαν Προγράμματα': u'No programmes found',
    u'Δεν βρέθηκαν Προγράμματα στον κάδο': u'No programmes found in Trash',
    u'Όλα τα Προγράμματα': u'All Programmes',
    u'Αρχείο Μεταπτυχιακών': u'Postgraduate Archive',
    u'Προγράμματα Μεταπτυχιακών Σπουδών (Π.Μ.Σ.).': u'Postgraduate study programmes (MSc / MA).',

    # Taxonomies.
    u'Σχολές (φίλτρο)': u'Schools (filter)',
    u'Σχολή (φίλτρο)': u'School (filter)',
    u'Φίλτρο Σχολής': u'School filter',
    u'Προσθήκη Σχολής': u'Add School',
    u'Συνδέει Ανακοινώσεις και Μεταπτυχιακά με μια Σχολή.':
        u'Links announcements and programmes to a school.',
    u'Κατηγορίες Ανακοινώσεων': u'Announcement Categories',
    u'Κατηγορία Ανακοίνωσης': u'Announcement Category',
    u'Κατηγορίες': u'Categories',
    u'Προσθήκη κατηγορίας': u'Add category',
    u'Τύποι Προγραμμάτων': u'Programme Types',
    u'Τύπος Προγράμματος': u'Programme Type',
    u'Τύποι': u'Types',
    u'Προσθήκη τύπου': u'Add type',

    # Meta fields.
    u'Διάρκεια': u'Duration',
    u'Πιστωτικές μονάδες (ECTS)': u'Credits (ECTS)',
    u'Δίδακτρα': u'Tuition fees',
    u'Προθεσμία αιτήσεων': u'Application deadline',
    u'Διευθυντής/-τρια Προγράμματος': u'Programme Director',
    u'Σύνδεσμος αίτησης': u'Application link',
    u'Κοσμήτορας': u'Dean',
    u'Τηλέφωνο': u'Phone',
    u'Email': u'Email',
    u'Διεύθυνση': u'Address',
    u'Ιστοσελίδα Σχολής': u'School website',
    u'Προθεσμία': u'Deadline',
    u'Συνημμένο αρχείο (URL)': u'Attached file (URL)',

    # SEO / γενικά.
    u'Αποτελέσματα αναζήτησης για: %s': u'Search results for: %s',
    u'Αρχική': u'Home',
    u'Card (3:2)': u'Card (3:2)',
    u'Hero (16:9)': u'Hero (16:9)',

    # Slider a11y.
    u'Παρουσίαση διαφανειών': u'Carousel',
    u'Προηγούμενη διαφάνεια': u'Previous slide',
    u'Επόμενη διαφάνεια': u'Next slide',
    u'Έναρξη αυτόματης εναλλαγής': u'Start autoplay',
    u'Παύση αυτόματης εναλλαγής': u'Pause autoplay',
    u'Διαφάνεια %1$s από %2$s': u'Slide %1$s of %2$s',
    u'Μετάβαση στη διαφάνεια %s': u'Go to slide %s',

    # Patterns / block styles.
    u'Κοσμητεία': u'Deanery',
    u'Ενότητες σελίδας για την ιστοσελίδα της Κοσμητείας.': u'Page sections for the Deanery website.',
    u'Κάρτες Κοσμητείας': u'Deanery cards',
    u'Κάρτα με σκιά': u'Card with shadow',
    u'Subfooter 4 στηλών': u'Four column subfooter',
    u'Κουμπί κύλισης (scroll)': u'Scroll down button',
    u'Zoom στο hover': u'Zoom on hover',
    u'Με υπογράμμιση': u'With underline',
    u'Περισσότερα για: %s': u'Read more about: %s',

    # Περιεχόμενο patterns.
    u'Πρόσφατες ανακοινώσεις': u'Latest announcements',
    u'Προκηρύξεις, προθεσμίες και νέα της ακαδημαϊκής κοινότητας.':
        u'Calls, deadlines and news from the academic community.',
    u'Όλες οι ανακοινώσεις': u'All announcements',
    u'Διαβάστε την ανακοίνωση': u'Read the announcement',
    u'Δεν υπάρχουν ανακοινώσεις αυτή τη στιγμή. Προσθέστε την πρώτη από:':
        u'There are no announcements yet. Add the first one from:',
    u'Ανακοινώσεις → Προσθήκη νέας': u'Announcements → Add New',
    u'Κεντρική παρουσίαση Κοσμητείας': u'Main Deanery carousel',
    u'Κοσμητεία Σχολών': u'Deanery of Schools',
    u'Σπουδές, έρευνα και καινοτομία σε τρεις Σχολές. Ανακαλύψτε τα προγράμματα και την ακαδημαϊκή μας κοινότητα.':
        u'Study, research and innovation across three schools. Explore our programmes and academic community.',
    u'Οι Σχολές μας': u'Our schools',
    u'Αιτήσεις μεταπτυχιακών σπουδών': u'Postgraduate applications',
    u'Δείτε τις ανοιχτές προκηρύξεις και τις προθεσμίες υποβολής για το νέο ακαδημαϊκό έτος.':
        u'See the open calls and submission deadlines for the new academic year.',
    u'Προκηρύξεις': u'Calls',
    u'Έρευνα με αντίκτυπο': u'Research with impact',
    u'Ερευνητικά εργαστήρια, διεθνείς συνεργασίες και προγράμματα κινητικότητας φοιτητών.':
        u'Research labs, international partnerships and student mobility programmes.',
    u'Τα νέα μας': u'Our news',
    u'Συνεχίστε την περιήγηση': u'Keep exploring',
    u'Μεταπτυχιακά προγράμματα': u'Postgraduate programmes',
    u'Προγράμματα Μεταπτυχιακών Σπουδών με ερευνητικό και επαγγελματικό προσανατολισμό.':
        u'Postgraduate programmes with a research and professional focus.',
    u'Όλα τα προγράμματα': u'All programmes',
    u'Πληροφορίες προγράμματος': u'Programme details',
    u'Δεν έχουν καταχωριστεί ακόμη προγράμματα. Προσθέστε το πρώτο από:':
        u'No programmes have been added yet. Add the first one from:',
    u'Μεταπτυχιακά → Προσθήκη νέου': u'Postgraduate → Add New',
    u'Τρεις Σχολές με διακριτή ταυτότητα, κοινό στόχο την ποιοτική εκπαίδευση και την έρευνα.':
        u'Three schools with a distinct identity and a shared commitment to quality education and research.',
    u'Δείτε τη Σχολή': u'Visit the school',
    u'Δεν έχουν καταχωριστεί ακόμη Σχολές. Προσθέστε την πρώτη από τον πίνακα ελέγχου:':
        u'No schools have been added yet. Add the first one from the dashboard:',
    u'Σχολές → Προσθήκη νέας': u'Schools → Add New',
    u'Παιδεία, έρευνα και κοινωνική προσφορά. Η Κοσμητεία συντονίζει τις Σχολές, τα προγράμματα σπουδών και την ακαδημαϊκή κοινότητα.':
        u'Education, research and public service. The Deanery coordinates the schools, the study programmes and the academic community.',
    u'Πλοήγηση': u'Navigation',
    u'Μενού υποσέλιδου 1': u'Footer menu 1',
    u'Η Κοσμητεία': u'The Deanery',
    u'Χρήσιμα': u'Useful links',
    u'Μενού υποσέλιδου 2': u'Footer menu 2',
    u'Φοιτητική μέριμνα': u'Student welfare',
    u'Κανονισμοί σπουδών': u'Study regulations',
    u'Προσβασιμότητα': u'Accessibility',
    u'Πολιτική απορρήτου': u'Privacy policy',
    u'Επικοινωνία': u'Contact',
    u'Πανεπιστημιούπολη, Κτίριο Διοίκησης': u'University Campus, Administration Building',
    u'Τ.Κ. 000 00, Πόλη': u'000 00, City',
    u'Ακολουθήστε μας': u'Follow us',
    u'© {{year}} {{site}}. Με επιφύλαξη παντός δικαιώματος.':
        u'© {{year}} {{site}}. All rights reserved.',
    u'Δήλωση προσβασιμότητας': u'Accessibility statement',
    u'Χάρτης ιστότοπου': u'Sitemap',

    # URL slugs (ίδια σε EL/EN, ώστε τα permalinks να μένουν σταθερά).
    u'schools': u'schools',
    u'announcements': u'announcements',
    u'programs': u'programs',
    u'faculty': u'faculty',
    u'announcement-category': u'announcement-category',
    u'program-type': u'program-type',
    # Φίλτρα ανακοινώσεων / Announcement filters.
    u'Αναζήτηση': u'Search',
    u'Λέξη-κλειδί, π.χ. υποτροφίες': u'Keyword, e.g. scholarships',
    u'Κατηγορία': u'Category',
    u'Όλες οι κατηγορίες': u'All categories',
    u'Όλα τα έτη': u'All years',
    u'Έτος': u'Year',
    u'Φιλτράρισμα': u'Filter',
    u'Καθαρισμός φίλτρων': u'Clear filters',
    u'Αναζήτηση και φίλτρα ανακοινώσεων': u'Announcement search and filters',
    u'Ο αριθμός αποτελεσμάτων εμφανίζεται στο front-end.':
        u'The result count is shown on the front end.',
    u'Καμία ανακοίνωση δεν ταιριάζει με τα φίλτρα.':
        u'No announcements match these filters.',
    u'Βρέθηκε 1 ανακοίνωση.': u'Found 1 announcement.',
    u'Βρέθηκαν %d ανακοινώσεις.': u'Found %d announcements.',
    u'Σύνολο: %d ανακοινώσεις.': u'%d announcements in total.',
    u'Πεδία φίλτρων': u'Filter fields',
    u'Πεδίο αναζήτησης': u'Search field',
    u'Φίλτρο κατηγορίας': u'Category filter',
    u'Φίλτρο Σχολής': u'School filter',
    u'Φίλτρο έτους': u'Year filter',
    u'Αριθμός αποτελεσμάτων': u'Result count',

    # Χάρτης / Map.
    u'Χάρτης (Leaflet)': u'Map (Leaflet)',
    u'Θέση στον χάρτη': u'Map position',
    u'Γεωγραφικό πλάτος (latitude)': u'Latitude',
    u'Γεωγραφικό μήκος (longitude)': u'Longitude',
    u'Δεξί κλικ σε σημείο του openstreetmap.org και «Show address» δίνει τις συντεταγμένες.':
        u'Right-click a spot on openstreetmap.org and choose "Show address" to read its coordinates.',
    u'Ζουμ': u'Zoom',
    u'ζουμ': u'zoom',
    u'Ύψος χάρτη (px)': u'Map height (px)',
    u'Πινέζα και συμπεριφορά': u'Marker and behaviour',
    u'Εμφάνιση πινέζας': u'Show marker',
    u'Τίτλος πινέζας': u'Marker title',
    u'Διεύθυνση': u'Address',
    u'Εμφανίζεται στην πινέζα και ως εναλλακτικό κείμενο χωρίς JavaScript.':
        u'Shown in the marker popup and as the fallback when JavaScript is unavailable.',
    u'Ζουμ με τη ρόδα του ποντικιού': u'Zoom with the mouse wheel',
    u'Απενεργοποιημένο ώστε η κύλιση της σελίδας να μη «κολλάει» στον χάρτη.':
        u'Off by default, so page scrolling is not trapped by the map.',
    u'Σύνδεσμος για οδηγίες πρόσβασης': u'Directions link',
    u'Ο διαδραστικός χάρτης εμφανίζεται στο front-end. Ρυθμίστε τη θέση από τις ρυθμίσεις του μπλοκ.':
        u'The interactive map appears on the front end. Set its position in the block settings.',
    u'Χωρίς τίτλο πινέζας': u'No marker title',
    u'Έλεγχος θέσης στο OpenStreetMap': u'Check the position on OpenStreetMap',
    u'Χάρτης τοποθεσίας': u'Location map',
    u'Σημείο στον χάρτη': u'Map location',
    u'Άνοιγμα στον χάρτη (OpenStreetMap)': u'Open in OpenStreetMap',
    u'Οδηγίες πρόσβασης': u'Directions',

    # Pattern "Επικοινωνία: στοιχεία και χάρτης".
    u'Πού θα μας βρείτε': u'Where to find us',
    u'Η Γραμματεία της Κοσμητείας στεγάζεται στο Κτίριο Διοίκησης της Πανεπιστημιούπολης. Η είσοδος είναι προσβάσιμη σε άτομα με αναπηρία.':
        u'The Deanery office is located in the Administration Building on campus. The entrance is wheelchair accessible.',
    u'Διεύθυνση: Πανεπιστημιούπολη, Κτίριο Διοίκησης':
        u'Address: University Campus, Administration Building',
    u'Ωράριο: Δευτέρα έως Παρασκευή, 09:00-14:00':
        u'Opening hours: Monday to Friday, 09:00-14:00',
    u'Μέσα μεταφοράς: αστικές γραμμές και στάση μετρό στην είσοδο της Πανεπιστημιούπολης':
        u'Transport: city bus lines and a metro stop at the campus entrance',
    u'Πανεπιστημιούπολη, Κτίριο Διοίκησης': u'University Campus, Administration Building',
    # Breadcrumbs.
    u'Αναζήτηση: %s': u'Search: %s',
    u'Η σελίδα δεν βρέθηκε': u'Page not found',
    u'Διαδρομή πλοήγησης': u'Breadcrumb',
    u'Η διαδρομή εμφανίζεται στις εσωτερικές σελίδες.':
        u'The breadcrumb trail appears on inner pages.',
    u'Σύνδεσμος Αρχικής': u'Home link',
    u'Τρέχουσα σελίδα': u'Current page',
    # Γκαλερί εικόνων & lightbox.
    u'Γκαλερί εικόνων': u'Image gallery',
    u'Εικόνες': u'Images',
    u'Πηγή εικόνων': u'Image source',
    u'Συνημμένα της σελίδας': u'Files attached to this page',
    u'Επιλεγμένες εικόνες': u'Selected images',
    u'Επιλέξτε εικόνες από τη Βιβλιοθήκη πολυμέσων.': u'Pick images from the Media Library.',
    u'Εμφανίζονται όσες εικόνες έχουν ανέβει μέσα από τη σελίδα, με τη σειρά μεταφόρτωσης.':
        u'Shows every image uploaded from this page, in upload order.',
    u'Χωρίς τη φωτογραφία της σελίδας': u'Skip the page photo',
    u'Η επιλεγμένη εικόνα (featured) δεν επαναλαμβάνεται μέσα στη γκαλερί.':
        u'The featured image is not repeated inside the gallery.',
    u'Τίτλος ενότητας': u'Section title',
    u'Προαιρετικός· εμφανίζεται μόνο όταν υπάρχουν εικόνες.':
        u'Optional; shown only when there are images.',
    u'Μέγιστο πλήθος εικόνων': u'Maximum number of images',
    u'0 = χωρίς όριο.': u'0 = no limit.',
    u'Στήλες σε μεγάλες οθόνες': u'Columns on large screens',
    u'Κενό ανάμεσα στις μικρογραφίες (px)': u'Gap between thumbnails (px)',
    u'Ελάχιστο πλάτος μικρογραφίας (px)': u'Minimum thumbnail width (px)',
    u'Καθορίζει πόσες μικρογραφίες χωρούν σε μικρές οθόνες.':
        u'Controls how many thumbnails fit on small screens.',
    u'Αναλογίες μικρογραφίας': u'Thumbnail aspect ratio',
    u'Τετράγωνο (1:1)': u'Square (1:1)',
    u'Οριζόντιο (4:3)': u'Landscape (4:3)',
    u'Οριζόντιο (3:2)': u'Landscape (3:2)',
    u'Πανοραμικό (16:9)': u'Widescreen (16:9)',
    u'Κατακόρυφο (3:4)': u'Portrait (3:4)',
    u'Φυσικές αναλογίες': u'Original proportions',
    u'Λεζάντες κάτω από τις μικρογραφίες': u'Captions below the thumbnails',
    u'Οι λεζάντες εμφανίζονται πάντα μέσα στο lightbox.':
        u'Captions always appear inside the lightbox.',
    u'Επεξεργασία εικόνων': u'Edit images',
    u'Επιλέξτε ή ανεβάστε τις εικόνες της γκαλερί.':
        u'Select or upload the gallery images.',
    u'Εμφανίζονται οι εικόνες που έχουν ανέβει μέσα από τη σελίδα, ως μικρογραφίες που ανοίγουν σε lightbox. Το αποτέλεσμα φαίνεται στο front-end.':
        u'Shows the images uploaded from this page as thumbnails that open in a lightbox. The result appears on the front end.',
    u'Στήλες: %1$s — αναλογίες: %2$s': u'Columns: %1$s — aspect ratio: %2$s',
    u'Δεν βρέθηκαν εικόνες για τη γκαλερί.': u'No images found for this gallery.',
    # Πεδίο «Φωτογραφίες (γκαλερί)» στη διαχείριση.
    u'Φωτογραφίες (γκαλερί)': u'Photos (gallery)',
    u'Επιλογή φωτογραφιών': u'Select photos',
    u'Χρήση αυτών των φωτογραφιών': u'Use these photos',
    u'Αφαίρεση όλων': u'Remove all',
    u'Αφαίρεση φωτογραφίας': u'Remove photo',
    u'Μετακίνηση πιο μπροστά': u'Move earlier',
    u'Μετακίνηση πιο πίσω': u'Move later',
    u'Δεν έχουν επιλεγεί φωτογραφίες.': u'No photos selected yet.',
    u'Να αφαιρεθούν όλες οι φωτογραφίες από τη γκαλερί;':
        u'Remove every photo from the gallery?',
    u'Οι φωτογραφίες εμφανίζονται ως μικρογραφίες στη σελίδα και ανοίγουν σε lightbox. Δεν έχουν σχέση με τις εικόνες που βάζετε μέσα στο κείμενο ούτε με την επιλεγμένη εικόνα.':
        u'These photos appear as thumbnails on the page and open in a lightbox. They are separate from the images inside the text and from the featured image.',
    u'Δεν έχουν επιλεγεί φωτογραφίες στο πεδίο «Φωτογραφίες (γκαλερί)» της σελίδας.':
        u'No photos selected in the page field "Photos (gallery)".',
    u'Ενότητα': u'Section',
    # Πολυμέσα: WebP / WebM.
    u'Τα βίντεο ανεβαίνουν μόνο σε μορφή WebM. Μετατρέψτε το αρχείο σε .webm και δοκιμάστε ξανά.':
        u'Videos can only be uploaded as WebM. Convert the file to .webm and try again.',
    u'Μετατροπή σε WebP': u'Convert to WebP',
    u'Η μετατροπή σε WebP απέτυχε.': u'The WebP conversion failed.',
    u'Το αρχείο δεν είναι JPEG ή PNG.': u'The file is not a JPEG or PNG.',
    u'Το αρχείο δεν είναι έγκυρη εικόνα.': u'The file is not a valid image.',
    u'Ο server δεν υποστηρίζει WebP.': u'This server cannot write WebP.',
    u'Το αρχείο δεν βρέθηκε.': u'The file was not found.',
    u'Μετατράπηκε %d αρχείο σε WebP.': u'Converted %d file to WebP.',
    u'Μετατράπηκαν %d αρχεία σε WebP.': u'Converted %d files to WebP.',
    u'%d αρχείο παραλείφθηκε.': u'%d file was skipped.',
    u'%d αρχεία παραλείφθηκαν.': u'%d files were skipped.',
    u'Οι φωτογραφίες επιλέγονται στο πεδίο «Φωτογραφίες (γκαλερί)», κάτω από το κείμενο της σελίδας. Εδώ ρυθμίζεται μόνο η εμφάνιση.':
        u'Photos are chosen in the "Photos (gallery)" field below the page content. This block only controls how they look.',
    u'Άνοιγμα φωτογραφίας σε μεγέθυνση: %s': u'Open larger photo: %s',
    u'Άνοιγμα φωτογραφίας σε μεγέθυνση': u'Open larger photo',
    u'Προβολή φωτογραφίας': u'Photo viewer',
    u"Κοσμητεία:": u"Deanery:",
    u"το θέμα χρειάζεται το πρόσθετο «Κοσμητεία Core» για τους τύπους περιεχομένου και τα μπλοκ του.": u"this theme needs the “Kosmiteia Core” plugin for its content types and blocks.",
    u"Ενεργοποίηση από τα Πρόσθετα": u"Activate it from Plugins",
    u"Ωράριο": u"Opening hours",
    u"Ρυθμίσεις": u"Settings",
    u"Εργαλεία": u"Tools",
    u"Εργαλεία Κοσμητείας": u"Deanery tools",
    u"Αποτέλεσμα": u"Result",
    u"Αρχικό περιεχόμενο": u"Starter content",
    u"Δημιουργεί Τμήματα, Ανακοινώσεις, Μεταπτυχιακά, σελίδες, μενού (EL/EN) και τα template parts της αρχικής, αντλώντας τα patterns από το ενεργό θέμα. Δεν αγγίζει ό,τι έχετε ήδη επεξεργαστεί.": u"Creates Departments, Announcements, Postgraduate programmes, pages, menus (EL/EN) and the home page template parts from the active theme's patterns. Anything you have already edited is left untouched.",
    u"Ξαναδημιουργία ακόμη κι αν υπάρχει ήδη": u"Rebuild even if it already exists",
    u"Δημιουργία αρχικού περιεχομένου": u"Create starter content",
    u"Εισαγωγή ανακοινώσεων από παλιό ιστότοπο": u"Import announcements from the old website",
    u"Διαβάζει το RSS feed του παλιού ιστότοπου και δημιουργεί Ανακοινώσεις με τις αρχικές ημερομηνίες, τις κατηγορίες τους και τα συνημμένα PDF. Ξανατρέξιμο δεν δημιουργεί διπλότυπα.": u"Reads the old website's RSS feed and creates Announcements with their original dates, categories and attached PDFs. Running it again does not create duplicates.",
    u"RSS feed": u"RSS feed",
    u"Σελίδες feed": u"Feed pages",
    u"10 ανακοινώσεις ανά σελίδα. Για πλήρη εισαγωγή προτιμήστε τη γραμμή εντολών: wp kosmiteia import-announcements": u"10 announcements per page. For a full import prefer the command line: wp kosmiteia import-announcements",
    u"Όριο ανακοινώσεων": u"Announcement limit",
    u"— καμία —": u"— none —",
    u"Κατάσταση": u"Status",
    u"Δημοσιευμένες": u"Published",
    u"Πρόχειρα": u"Drafts",
    u"Επιλογές": u"Options",
    u"Λήψη συνημμένων (PDF, εικόνες) στη Βιβλιοθήκη": u"Download attachments (PDFs, images) to the Media Library",
    u"Δοκιμή χωρίς εγγραφή στη βάση": u"Dry run, without writing to the database",
    u"Έναρξη εισαγωγής": u"Start import",
    u"© %1$s %2$s": u"© %1$s %2$s",
    u"Τηλ. %s": u"Phone %s",
    u"Email: %s": u"Email: %s",
    u"Ωράριο: %s": u"Hours: %s",
    u"Ρυθμίσεις Κοσμητείας": u"Deanery settings",
    u"Το αρχικό περιεχόμενο υπάρχει ήδη - δεν έγινε καμία αλλαγή.": u"Starter content already exists - nothing was changed.",
    u"ΔΟΚΙΜΗ (dry run): δεν γράφεται τίποτα στη βάση.": u"DRY RUN: nothing is written to the database.",
    u"Δεν βρέθηκαν ανακοινώσεις στο feed.": u"No announcements were found in the feed.",
    u"Εκδηλώσεις": u"Events",
    u"Εκδήλωση": u"Event",
    u"Προσθήκη νέας Εκδήλωσης": u"Add new Event",
    u"Επεξεργασία Εκδήλωσης": u"Edit Event",
    u"Νέα Εκδήλωση": u"New Event",
    u"Προβολή Εκδήλωσης": u"View Event",
    u"Προβολή Εκδηλώσεων": u"View Events",
    u"Αναζήτηση Εκδηλώσεων": u"Search Events",
    u"Δεν βρέθηκαν Εκδηλώσεις": u"No Events found",
    u"Δεν βρέθηκαν Εκδηλώσεις στον κάδο": u"No Events found in Trash",
    u"Όλες οι Εκδηλώσεις": u"All Events",
    u"Ημερολόγιο Εκδηλώσεων": u"Events calendar",
    u"Ημερίδες, συνέδρια, ορκωμοσίες και άλλες εκδηλώσεις.": u"Workshops, conferences, graduation ceremonies and other events.",
    u"events": u"events",
    u"Σύντομη περιγραφή της εκδήλωσης...": u"Short description of the event...",
    u"Προσωπικό": u"Staff",
    u"Μέλος": u"Member",
    u"Προσθήκη νέου μέλους": u"Add new member",
    u"Επεξεργασία μέλους": u"Edit member",
    u"Νέο μέλος": u"New member",
    u"Προβολή μέλους": u"View member",
    u"Προβολή μελών": u"View members",
    u"Αναζήτηση μελών": u"Search members",
    u"Δεν βρέθηκαν μέλη": u"No members found",
    u"Δεν βρέθηκαν μέλη στον κάδο": u"No members found in Trash",
    u"Όλα τα μέλη": u"All members",
    u"Κατάλογος προσωπικού": u"Staff directory",
    u"Φωτογραφία μέλους": u"Member photo",
    u"Ορισμός φωτογραφίας": u"Set photo",
    u"Μέλη Δ.Ε.Π., Ε.ΔΙ.Π., Ε.Τ.Ε.Π., διοικητικό προσωπικό και συλλογικά όργανα.": u"Faculty, teaching and laboratory staff, administrative staff and collective bodies.",
    u"people": u"people",
    u"Έγγραφα": u"Documents",
    u"Έγγραφο": u"Document",
    u"Προσθήκη νέου Εγγράφου": u"Add new Document",
    u"Επεξεργασία Εγγράφου": u"Edit Document",
    u"Νέο Έγγραφο": u"New Document",
    u"Προβολή Εγγράφου": u"View Document",
    u"Προβολή Εγγράφων": u"View Documents",
    u"Αναζήτηση Εγγράφων": u"Search Documents",
    u"Δεν βρέθηκαν Έγγραφα": u"No Documents found",
    u"Δεν βρέθηκαν Έγγραφα στον κάδο": u"No Documents found in Trash",
    u"Όλα τα Έγγραφα": u"All Documents",
    u"Αρχείο Εγγράφων": u"Document archive",
    u"Κανονισμοί, έντυπα, αποφάσεις και οδηγοί σπουδών προς λήψη.": u"Regulations, forms, decisions and study guides available for download.",
    u"documents": u"documents",
    u"Είδη εκδηλώσεων": u"Event types",
    u"Είδος εκδήλωσης": u"Event type",
    u"Είδη": u"Types",
    u"Προσθήκη είδους": u"Add type",
    u"event-type": u"event-type",
    u"Κατηγορίες προσωπικού": u"Staff categories",
    u"Κατηγορία προσωπικού": u"Staff category",
    u"staff-group": u"staff-group",
    u"Είδη εγγράφων": u"Document types",
    u"Είδος εγγράφου": u"Document type",
    u"document-type": u"document-type",
    u"Έναρξη (ΕΕΕΕ-ΜΜ-ΗΗ ΩΩ:ΛΛ)": u"Start (YYYY-MM-DD HH:MM)",
    u"Λήξη (ΕΕΕΕ-ΜΜ-ΗΗ ΩΩ:ΛΛ)": u"End (YYYY-MM-DD HH:MM)",
    u"Τόπος διεξαγωγής": u"Venue",
    u"Σύνδεσμος δήλωσης συμμετοχής": u"Registration link",
    u"Διαδικτυακή εκδήλωση (ναι/όχι)": u"Online event (yes/no)",
    u"Ιδιότητα / βαθμίδα": u"Position / rank",
    u"Γραφείο": u"Office",
    u"Σύνδεσμος βιογραφικού": u"CV link",
    u"ORCID": u"ORCID",
    u"Αρχείο (URL)": u"File (URL)",
    u"Αριθμός πρωτοκόλλου / ΑΔΑ": u"Protocol number / ADA",
    u"Ημερομηνία εγγράφου": u"Document date",
    u"Ημερομηνία": u"Date",
    u"Ιδιότητα": u"Position",
    u"Αρχείο": u"File",
    u"Ταυτότητα": u"Identity",
    u"Ίδρυμα": u"Institution",
    u"Δημοκρίτειο Πανεπιστήμιο Θράκης": u"Democritus University of Thrace",
    u"Εμφανίζεται στο υποσέλιδο και στα structured data.": u"Shown in the footer and in structured data.",
    u"Ιδιότητα Κοσμήτορα": u"Dean's title",
    u"Καθηγητής": u"Professor",
    u"Μότο υποσέλιδου": u"Footer tagline",
    u"Κείμενο copyright": u"Copyright text",
    u"Αν μείνει κενό συντίθεται αυτόματα: «© έτος - όνομα ιστότοπου».": u"If left empty it is composed automatically: “© year - site name”.",
    u"Fax": u"Fax",
    u"Ωράριο εξυπηρέτησης": u"Office hours",
    u"Δευτέρα έως Παρασκευή, 09:00-14:00": u"Monday to Friday, 09:00-14:00",
    u"Σημείωση πρόσβασης": u"Access note",
    u"Π.χ. οδηγίες πρόσβασης ή προσβασιμότητα κτηρίου.": u"E.g. directions or building accessibility.",
    u"Χάρτης": u"Map",
    u"Γεωγραφικό πλάτος": u"Latitude",
    u"Γεωγραφικό μήκος": u"Longitude",
    u"Zoom": u"Zoom",
    u"Κοινωνικά δίκτυα": u"Social networks",
    u"Facebook": u"Facebook",
    u"Instagram": u"Instagram",
    u"YouTube": u"YouTube",
    u"LinkedIn": u"LinkedIn",
    u"X / Twitter": u"X / Twitter",
    u"Περιεχόμενο": u"Content",
    u"Ανακοινώσεις ανά σελίδα": u"Announcements per page",
    u"Ισχύει στο αρχείο Ανακοινώσεων και στα φίλτρα του.": u"Applies to the Announcements archive and its filters.",
    u"Εκδηλώσεις ανά σελίδα": u"Events per page",
    u"Λέξεις περίληψης": u"Excerpt words",
    u"Απόκρυψη περασμένων εκδηλώσεων": u"Hide past events",
    u"Στο αρχείο Εκδηλώσεων εμφανίζονται μόνο οι επόμενες.": u"Only upcoming events are listed in the Events archive.",
    u"Γλώσσες": u"Languages",
    u"Αγγλική έκδοση": u"English version",
    u"Ενεργοποιεί τον επιλογέα γλώσσας και τα αγγλικά template parts (?lang=en).": u"Enables the language switcher and the English template parts (?lang=en).",
    u"Ναι": u"Yes",
    u"Τα στοιχεία αυτά τροφοδοτούν το υποσέλιδο, τη σελίδα επικοινωνίας, τον χάρτη και τα δομημένα δεδομένα. Δεν χρειάζεται επέμβαση σε templates.": u"These details feed the footer, the contact page, the map and the structured data. No template editing needed.",
    u"Συντάκτης Ανακοινώσεων": u"Announcements editor",
    u'Προηγούμενη φωτογραφία': u'Previous photo',
    u'Επόμενη φωτογραφία': u'Next photo',
    u'Κλείσιμο': u'Close',
    u'%1$s από %2$s': u'%1$s of %2$s',
}

STRING_PATTERN = re.compile(r"(?:esc_html_e|esc_attr_e|esc_html__|esc_attr__|_e|__|_x)\(\s*'([^']*)'")


def collect_strings():
    """Μαζεύει τα μεταφράσιμα strings από PHP και JS."""
    files = source_files()

    found = []
    for path in files:
        source = io.open(path, encoding='utf-8').read()
        for match in STRING_PATTERN.finditer(source):
            value = match.group(1)
            if value and value not in found:
                found.append(value)
    return found


PLURAL_PATTERN = re.compile(r"_n\(\s*'([^']*)'\s*,\s*'([^']*)'")


def collect_plurals():
    """Μαζεύει τα ζεύγη ενικού/πληθυντικού από τις κλήσεις _n()."""
    files = source_files()

    found = []
    for path in files:
        source = io.open(path, encoding='utf-8').read()
        for match in PLURAL_PATTERN.finditer(source):
            pair = (match.group(1), match.group(2))
            if pair[0] and pair[1] and pair not in found:
                found.append(pair)
    return found


def po_escape(value):
    return value.replace('\\', '\\\\').replace('"', '\\"').replace('\n', '\\n')


def write_po(path, entries, header, plurals=()):
    lines = [header]
    for msgid, msgstr in entries:
        lines.append(u'msgid "%s"' % po_escape(msgid))
        lines.append(u'msgstr "%s"' % po_escape(msgstr))
        lines.append(u'')
    for single, plural, one, many in plurals:
        lines.append(u'msgid "%s"' % po_escape(single))
        lines.append(u'msgid_plural "%s"' % po_escape(plural))
        lines.append(u'msgstr[0] "%s"' % po_escape(one))
        lines.append(u'msgstr[1] "%s"' % po_escape(many))
        lines.append(u'')
    io.open(path, 'w', encoding='utf-8', newline='\n').write(u'\n'.join(lines))


def write_mo(path, catalog):
    """Γράφει αρχείο .mo (little endian) - χωρίς εξαρτήσεις από msgfmt."""
    items = sorted((k.encode('utf-8'), v.encode('utf-8')) for k, v in catalog.items())
    count = len(items)
    key_start = 7 * 4 + 16 * count
    value_start = key_start + sum(len(k) + 1 for k, _ in items)

    key_offsets = []
    value_offsets = []
    offset = key_start
    for key, _value in items:
        key_offsets.append((len(key), offset))
        offset += len(key) + 1
    offset = value_start
    for _key, value in items:
        value_offsets.append((len(value), offset))
        offset += len(value) + 1

    output = struct.pack('<7I', 0x950412de, 0, count, 7 * 4, 7 * 4 + count * 8, 0, 0)
    for length, off in key_offsets:
        output += struct.pack('<2I', length, off)
    for length, off in value_offsets:
        output += struct.pack('<2I', length, off)
    for key, _value in items:
        output += key + b'\x00'
    for _key, value in items:
        output += value + b'\x00'

    with open(path, 'wb') as handle:
        handle.write(output)


def main():
    languages_dir = os.path.join(HERE, 'languages')
    if not os.path.isdir(languages_dir):
        os.makedirs(languages_dir)

    strings = collect_strings()
    plurals = collect_plurals()
    missing = [s for s in strings if s not in EN]
    missing += [s for pair in plurals for s in pair if s not in EN]

    pot_header = (
        u'# Kosmiteia - πρότυπο μεταφράσεων.\n'
        u'msgid ""\n'
        u'msgstr ""\n'
        u'"Project-Id-Version: Kosmiteia 1.0.0\\n"\n'
        u'"Content-Type: text/plain; charset=UTF-8\\n"\n'
        u'"Content-Transfer-Encoding: 8bit\\n"\n'
        u'"Language: \\n"\n'
        u'"Plural-Forms: nplurals=2; plural=(n != 1);\\n"\n'
    )
    write_po(
        os.path.join(languages_dir, '%s.pot' % TEXTDOMAIN),
        [(s, u'') for s in strings],
        pot_header,
        [(single, plural, u'', u'') for single, plural in plurals],
    )

    en_header = pot_header.replace(u'"Language: \\n"', u'"Language: en_US\\n"')
    catalog = {}
    entries = []
    for source in strings:
        target = EN.get(source, u'')
        entries.append((source, target))
        if target:
            catalog[source] = target

    # Πληθυντικοί: στο .mo το κλειδί είναι "ενικός\0πληθυντικός".
    plural_entries = []
    for single, plural in plurals:
        one = EN.get(single, u'')
        many = EN.get(plural, u'')
        plural_entries.append((single, plural, one, many))
        if one and many:
            catalog[single + u'\0' + plural] = one + u'\0' + many

    catalog[u''] = (
        u'Content-Type: text/plain; charset=UTF-8\n'
        u'Language: en_US\n'
        u'Plural-Forms: nplurals=2; plural=(n != 1);\n'
    )

    write_po(os.path.join(languages_dir, 'en_US.po'), entries, en_header, plural_entries)
    write_mo(os.path.join(languages_dir, 'en_US.mo'), catalog)

    # Το ίδιο κατάλογο και στο πρόσθετο, ώστε να μεταφράζονται και τα δικά του
    # strings όταν το WordPress φορτώσει το textdomain του.
    plugin_languages = os.path.join(PLUGIN, 'languages')
    if os.path.isdir(PLUGIN):
        if not os.path.isdir(plugin_languages):
            os.makedirs(plugin_languages)
        write_po(os.path.join(plugin_languages, '%s.pot' % TEXTDOMAIN),
                 [(s, u'') for s in strings], pot_header,
                 [(single, plural, u'', u'') for single, plural in plurals])
        # Τα plugins ονομάζουν τα αρχεία «<textdomain>-<locale>.mo»· τα themes
        # μόνο «<locale>.mo». Χωρίς τη σωστή ονομασία το WordPress δεν φορτώνει
        # τις μεταφράσεις του προσθέτου.
        write_po(os.path.join(plugin_languages, '%s-en_US.po' % TEXTDOMAIN), entries, en_header, plural_entries)
        write_mo(os.path.join(plugin_languages, '%s-en_US.mo' % TEXTDOMAIN), catalog)

    summary = {
        'strings': len(strings) + len(plurals),
        'translated': len(catalog) - 1,
        'missing': missing,
    }
    io.open(os.path.join(HERE, 'strings.json'), 'w', encoding='utf-8').write(
        json.dumps(summary, ensure_ascii=False, indent=1)
    )
    print('strings: %d (+%d plural), translated: %d, missing: %d' % (
        len(strings), len(plurals), len(catalog) - 1, len(missing)))


if __name__ == '__main__':
    main()
