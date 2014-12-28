<?php
require_once($config['root'].'modules/html2pdf/html2pdf.class.php');

class PdfGenerator {

	function __construct(){
	    $this->html2pdf = new HTML2PDF('P','A4','fr');
	}
	
	function generatePdf($pContent, $pFileName){
	    
	    
	    $this->html2pdf->WriteHTML($pContent);
	    return $this->html2pdf->Output($pFileName, "F");
	}

}

?>