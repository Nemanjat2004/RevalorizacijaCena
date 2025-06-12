<?php

require_once 'db.class.7.php';
//include 'loging_provera.php';

$db = DBstambena();

$get = $_GET['get'] ?? '';

$data = [];

if ($get == "ugovor") {

    $sql = "SELECT
            ugovori.id,
            ugovori.datumDok,
            ugovori.Cena,
            ugovori.Ucesce,
            ugovori.BrojRata,
            ugovori.Aktivan
        FROM ugovori";

    $result = $db->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $db->fetchNextObject($result)) {
            $formattedDatum = date('d.m.Y', strtotime($row->datumDok));
            $data[]         = [
                'id'       => $row->id,
                'datum'    => $formattedDatum,
                'Cena'     => $row->Cena,
                'Ucesce'   => $row->Ucesce,
                'BrojRata' => $row->BrojRata,
                'Aktivan'  => $row->Aktivan ? 'Da' : 'Ne',
            ];
        }
    }

} elseif ($get == 'rate') {
    $ugovor_id = $_GET['ugovor_id'] ?? '';

    $sql         = "SELECT redni_broj, cena, rev_cena, datum FROM rate WHERE id_ugovora = ?";
    $result_rate = $db->prepareAndExecute($sql, "i", $ugovor_id);

    $sql              = "SELECT SUM(Dug) AS ukupno_potrazuje FROM izvodstavke WHERE id_ugovora = ?";
    $result_potrazuje = $db->prepareAndExecute($sql, "i", $ugovor_id);
    $potrazuje        = ($db->fetchNextObject($result_potrazuje))->ukupno_potrazuje;

    $row = $db->fetchNextObject($result_rate);
    if (! $row) {
        echo json_encode([]);
        exit;
    }

    $ukupno_placeno = 0;
    $data           = [];

    do {
        $iznos_za_racunanje = ($row->rev_cena && $row->rev_cena > 0) ? $row->rev_cena : $row->cena;

        if ($potrazuje > 0) {
            if ($iznos_za_racunanje > $potrazuje) {
                $placeno = $potrazuje;
            } else {
                $placeno = $iznos_za_racunanje;
            }
            $potrazuje -= $placeno;
        } else {
            $placeno = 0;
        }

        $ukupno_placeno += $placeno;

        $data[] = [
            'id'             => $row->id ?? null,
            'redni_broj'     => $row->redni_broj,
            'cena'           => $row->cena,
            'rev_cena'       => $row->rev_cena,
            'datum'          => date('d.m.Y', strtotime($row->datum)),
            'placeno'        => round($placeno, 2),
            'ukupno_placeno' => round($ukupno_placeno, 2),
        ];
    } while ($row = $db->fetchNextObject($result_rate));

} elseif ($get == 'stavke') {

    $sql           = "SELECT id, id_ugovora, Dug FROM izvodstavke WHERE 1";
    $result_stavke = $db->query($sql);

    $data = [];
    if ($result_stavke && $db->numRows($result_stavke) > 0) {
        while ($row = $db->fetchNextObject($result_stavke)) {
            $data[] = [
                'id'         => $row->id,
                'id_ugovora' => $row->id_ugovora,
                'Dug'        => round($row->Dug, 2),
            ];
        }
    } else {
        $data = ['error' => 'No stavke found for this id_ugovora.'];
    }
} elseif ($get == 'revalorizacija') {
    $sql = "SELECT r.id, r.datum_rev, r.napomena, r.koef,
                   CASE WHEN ru.id_revalorizacije IS NOT NULL THEN 'Aktivna' ELSE 'Neaktivna' END AS aktivna
            FROM revalorizacija r
            LEFT JOIN rev_ugovor ru ON r.id = ru.id_revalorizacije";

    $result_revalorizacija = $db->query($sql);

    if ($result_revalorizacija && $result_revalorizacija->num_rows > 0) {
        while ($row = $db->fetchNextObject($result_revalorizacija)) {
            $data[] = [
                'id'       => $row->id,
                'datum'    => date('d.m.Y', strtotime($row->datum_rev)),
                'napomena' => $row->napomena,
                'koef'     => rtrim($row->koef, "0"),
                'refresh'  => '<button class="btn btn-sm btn-primary refresh-btn" onclick="refreshRevalorizacija(' . $row->id . ')" title="Osveži revalorizaciju" data-id="' . $row->id . '"><i class="bi bi-arrow-clockwise"></i> Osveži</button>  <button class="btn btn-sm btn-danger delete-btn" onclick="deleteRevalorizacija(' . $row->id . ')" title="Obriši revalorizaciju" data-id="' . $row->id . '"><i class="bi bi-trash"></i> Obriši</button>',
            ];
        }
    } else {
        $data = ['error' => 'No revalorizacija found.'];
    }
} else {
    $data = ['error' => 'Nepoznat parametar get: ' . $get];
}
header('Content-Type: application/json');
echo json_encode($data);
$db->close();
exit;
