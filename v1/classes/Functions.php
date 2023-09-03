<?php

Class Functions
{

    public function sanitize($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

        return $data;
    }

    public function genAlphabetNumbers($type, $length)
    {
        $alphabets = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '1234567890';
        $characters = ($type == "A") ? $alphabets : $numbers;
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function randomStr($length)
    {
        // $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $characters = '1234567890';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function genOrderRefId()
    {
        // this . randomString(3, 'A')+'-'+this . randomString(8, '#') + this . randomString(5, '#');

        return $this->genAlphabetNumbers('A', 3) . "-" . $this->randomStr(8) . $this->genAlphabetNumbers('#', 5);
    }

    public function genRefCode($length = 7)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function generate_csv_from_array( $array, $filename = "export.csv", $delimiter="," )
    {
        ob_start();
        header( 'Content-Type: application/csv; charset=UTF-8' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '";' );
    
        // clean output buffer
        ob_end_clean();
        
        $handle = fopen( 'php://output', 'w' );
    
        foreach ( $array as $value ) {
            fputcsv( $handle, $value, $delimiter );
        }
    
        fclose( $handle );
    
        // flush buffer
        ob_flush();
        
        // use exit to get rid of unexpected output afterward
        exit();
    }

    public function generate_pdf($filename = "export.pdf") {
        ob_end_clean();
        require('vendor/fpdf/fpdf.php');
        
        // Instantiate and use the FPDF class 
        $pdf = new FPDF();
        
        //Add a new page
        $pdf->AddPage();
        
        // Set the font for the text
        $pdf->SetFont('Arial', 'B', 18);
        
        // Prints a cell with given text 
        $pdf->Cell(60,20,'Scallat Invoice');
        // Set the font for the text
        $pdf->SetFont('Arial', '', 14);
        $pdf->Cell(60,20,'Invoice To: ');
        
        // return the generated output
        $pdf->Output();
    }   

    function createFolder($folderPath) {
        // $folderPath = '/path/to/folder';

        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0777, true);
            // echo 'Folder created successfully.';
        } else {
            // echo 'Folder already exists.';
        }
    }
}