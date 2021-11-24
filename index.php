<?php

const EMAIL = 'elhayatschool.com';

$row = 1;

$shortopts  = "f:";
$longopts  = array(
    "required:",     // Required value
);
$options = getopt($shortopts, $longopts);

$csv_name = $options['f'];

$array = $fields = array();
$i = 0;

$handle = @fopen("./csv_here/$csv_name", "r");

if ($handle) {
    while (($row = fgetcsv($handle, 4096)) !== false) {
        if (empty($fields)) {
            $fields = $row;
            continue;
        }
        foreach ($row as $k => $value) {
            $array[$i][$fields[$k]] = $value;
        }
        $i++;
    }
    if (!feof($handle)) {
        echo "Error: unexpected fgets() fail\n";
    }
    fclose($handle);
}
// echo "<pre>";
// var_dump($array);
// echo "</pre>";
// die;

require_once __DIR__ . '/vendor/autoload.php';

$mpdf = new \Mpdf\Mpdf([
    'orientation' => 'P'
]);

$mpdf->autoScriptToLang = true;
$mpdf->autoLangToFont = true;
$mpdf->SetDirectionality('rtl');

$style_data = file_get_contents("style.css");
$text_ar = file_get_contents("arab.html");

// Write the stylesheet
$mpdf->WriteHTML($style_data, 1);   // The parameter 1 tells mPDF that this is CSS and not HTML


for ($i = 0; $i < sizeof($array); $i++) {
    $student_row = $array[$i];

    $email = $student_row['mail'] . "@" . EMAIL;

    $student = "
    <table>
        <tr>
            <td>اللقب</td>
            <td>الاسم</td>
            <td>القسم</td>
            <td>البريد الالكتروني</td>
            <td>كلمة السر</td>
        </tr>
        <tr>
            <td>{$student_row['nom']}</td>
            <td>{$student_row['prenom']}</td>
            <td>{$student_row['class']}</td>
            <td>$email</td>
            <td>{$student_row['pass']}</td>
        </tr>
    </table>
    ";

    $mpdf->WriteHTML($text_ar, 2);
    $mpdf->WriteHTML($student, 2);
    $mpdf->WriteHTML('<hr>', 2);

    // Ensure that a page show more than 4 records
    if (($i + 1) % 4 === 0) {
        $mpdf->AddPage();
    }
}

// Set the metadata
$mpdf->SetTitle("Compte d'émails des étudiants");
$mpdf->SetAuthor("Boudouma Mohamed Ilies");
$mpdf->SetCreator("mPDF - medilies");
$mpdf->SetSubject("Compte émails");
$mpdf->SetKeywords("crédentials");

// Web response
// $mpdf->Output();

// Dump file
$mpdf->Output("./csv_here/$csv_name.pdf", 'F');
