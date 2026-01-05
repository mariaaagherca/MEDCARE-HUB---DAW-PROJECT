# MEDCARE-HUB---DAW-PROJECT

**MEDCARE HUB** este o aplicație web dezvoltată în PHP care simulează o platformă medicală pentru gestionarea pacienților și doctorilor, cu accent pe securitate, administrare, comunicare și analiză de date.

---

## 🔐 Autentificare & Securitate

* Sistem complet de **înregistrare și autentificare**
* Parole criptate (`password_hash`)
* **Roluri diferite**:

  * Administrator
  * Doctor
  * Patient
* Protecție împotriva:

  * SQL Injection (prepared statements)
  * CSRF (token-uri)
  * XSS (escape output)
  * Form Spoofing
* **reCAPTCHA** pentru formulare publice
* Logout și terminare corectă a sesiunii
* Resetare parolă prin email cu **cod de verificare**

---

## 👥 Roluri și funcționalități

### 🛡️ Administrator

* Aprobare conturi pacienți (cu email automat)
* Creare conturi de doctor (email + parolă trimisă)
* Vizualizare și gestionare pacienți și doctori
* Asignare / schimbare doctor pentru pacienți
* Ștergere pacienți și doctori
* Gestionare cereri de suport
* Acces la **Analytics**
* Generare rapoarte PDF
* Integrare date medicale externe

### 🩺 Doctor

* Vizualizare pacienți asignați
* Vizualizare profil pacient

### 🧑‍⚕️ Patient

* Completare și editare profil
* Vizualizare doctor asignat
* Trimitere mesaje de suport
* Autentificare după aprobare

---

## 📄 Export & Rapoarte

* Export **PDF** folosind DOMPDF:

  * Profil pacient
  * Profil doctor
  * Rapoarte Analytics
* Layout personalizat și denumiri sugestive

---

## 📧 Email & Comunicare

* Trimitere email cu **PHPMailer**
* Email-uri automate pentru:

  * Aprobare cont
  * Creare cont doctor
  * Resetare parolă
  * Răspuns suport
* Formular Help cu email + mesaj + captcha

---

## 📊 Website Analytics

* Logare vizite în pagini importante
* Identificare utilizator / guest
* Statistici:

  * Total vizite
  * Vizite zilnice
  * Top pagini
* Export raport PDF

---

## 🌐 Integrare date externe

* Afișare informații medicale din surse externe
* Date procesate intern (fără iframe)

---

## 🗄️ Bază de date

* MySQL
* Operații CRUD complete
* Tabele:

  * users
  * patients
  * doctors
  * patient_assignments
  * page_visits
  * help_requests
  * password_resets
