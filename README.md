# Revalorizacija Cena - Web aplikacija

Ovaj projekat prikazuje kako se vrši revalorizacija cena na osnovu iznosa iz ugovora i koeficijenata promene vrednosti.

## Funkcionalnosti:

- Unos osnovne cene
- Automatsko izračunavanje revalorizovane cene
- Prikaz razlike pre/posle revalorizacije
- Datum ugovora se koristi za računanje koeficijenta

## Tehnologije:

- PHP
- MySQL
- JavaScript
- HTML/CSS

## Kako pokrenuti:

1. Importuj `table.sql` u MySQL
2. Pokreni lokalni server (XAMPP/Laragon)
3. Proveriti da li su unesene stavke izvoda i ugovori(u slucaju da nisu potrebno je uneti sa datumim postojeceg ugovora)
4. Pokrenuti admin/rate.php - strana ce generisati rate u tabeli rate
5. Kreirati revalorizaciju potom pritisnuti polje "Da,zelim"(ukoliko imate problem sa tim imate opciju da se azurira nakrandno)
6. Sirenjem tabele ugovori imacete prikaz rata kao i revalorizovane vrednosti rata
   
## Autor:

Nemanja Trpčević
