<?php

require_once '../db.class.7.php';

$db = DBstambena();

// Fetch all ugovori
$sql_ugovori    = "SELECT id, BrojRata, Cena, datumDok FROM ugovori";
$result_ugovori = $db->query($sql_ugovori);

if ($result_ugovori && $result_ugovori->num_rows > 0) {
    while ($ugovor = $db->fetchNextObject($result_ugovori)) {
        $id_ugovora    = $ugovor->id;
        $broj_rata     = (int) $ugovor->BrojRata;
        $cena          = (float) $ugovor->Cena;
        $datum_pocetka = $ugovor->datumDok ?: date('Y-m-d');

        if ($broj_rata > 0) {
            $rata_iznos = round($cena / $broj_rata, 2);

            // Insert rates for this ugovor
            for ($i = 0; $i < $broj_rata; $i++) {
                // Calculate datum in SQL for correct month increment
                $sql_insert = "
                    INSERT INTO rate (id_ugovora, redni_broj, cena, rev_cena, datum)
                    VALUES (?, ?, ?, ?, DATE_ADD(?, INTERVAL ? MONTH))
                ";
                $db->prepareAndExecute($sql_insert, "iiddsi", $id_ugovora, $i + 1, $rata_iznos, $rata_iznos, $datum_pocetka, $i);
            }
        }
    }
    echo "Rate su uspešno kreirane za sve ugovore.";
} else {
    echo "Nema ugovora za obradu.";
}

$db->close();
