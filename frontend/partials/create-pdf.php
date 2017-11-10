<?php
include_once('../../../../../wp-load.php');
define("DOMPDF_FONT_HEIGHT_RATIO", 0.75);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	$projects = $_POST['projects'];
	if($projects != null) {
		$html = stripslashes( '<style>'.file_get_contents( plugins_url( '/reference-configurator/frontend/css/pdf_style.css' ) ).'</style>' );
		$html .= '<div class="divider"></div><div class="footer"><img src="' . plugins_url( "/reference-configurator/frontend/assets/images/logo.png" ) . '" alt="footer_logo"/></div>';
		$html .= '<div class="header_logo"><img src="' . plugins_url( "/reference-configurator/frontend/assets/images/logo.png" ) . '" alt="logo"/></div>';
		$html .= '<div class="header_image"><img src="' . plugins_url( "/reference-configurator/frontend/assets/images/main_image.png" ) . '" alt="main_image"/></div>';

		foreach ( $projects as $p ) {
			$html .= stripslashes( $p['details'] );
		}

		$html .= '<div class="footer_image"><img src="' . plugins_url( "/reference-configurator/frontend/assets/images/footer_image.png" ) . '" alt="footer_image"/></div>';

		$dompdf->loadHtml( $html );
		$dompdf->setPaper( 'A4', 'landscape' );
		$dompdf->render();
		$output = $dompdf->output();

		$date     = new DateTime();
		$date     = $date->format( 'Y-m-d' );
		$filename = uniqid( rand(), false ) . '.pdf';
		$filename = 'ref-' . $date . '-' . $filename;

		$upload_dir = wp_upload_dir();
		$path_dir   = $upload_dir['basedir'] . '/pdfs/' . $filename;
		$path_url   = $upload_dir['baseurl'] . '/pdfs/' . $filename;


		file_put_contents( $path_dir, $output );

		if(isset($_POST['email']))
		{
			$email = $_POST['email'];
			$subject = $_POST['subject'];
			$message = $_POST['message'];

			$attachments = array();
			array_push($attachments, $path_dir);

			$headers[] = "Content-type: text/html";
			$headers[] = 'From: Assmont <info@assmont.dev>';
			$debug = wp_mail($email, $subject, $message, $headers, $attachments );

			wp_send_json( array(
					'path'  => $path_url,
					'name'  => $filename,
					'debug' => $debug,
					'email' => $email,
					'subject' => $subject,
					'message' => $message
				)
			);

		}
		else {
			wp_send_json( array(
					'path'  => $path_url,
					'name'  => $filename,
					'debug' => $html
				)
			);
		}
	}
}

?>