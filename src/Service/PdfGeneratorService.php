<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Product;
use Symfony\Component\HttpFoundation\File\File;

class PdfGeneratorService
{
    private string $pdfDirectory;

    public function __construct(string $pdfDirectory = '/public/pdf')
    {
        $this->pdfDirectory = $pdfDirectory;
    }

    public function generateProductPdf(Product $product): string
    {
        // Configuration de Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);

        // Contenu du PDF
        $html = "
            <h1>Fiche Produit</h1>
            <p><strong>Nom :</strong> {$product->getName()}</p>
            <p><strong>Description :</strong> {$product->getDescription()}</p>
            <p><strong>Prix :</strong> {$product->getPrice()} €</p>
            <p><strong>Date de création :</strong> {$product->getCreatedAt()->format('d/m/Y H:i:s')}</p>
        ";

        // Générer le PDF
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Assurer l'existence du dossier
        if (!is_dir($this->pdfDirectory)) {
            mkdir($this->pdfDirectory, 0777, true);
        }

        // Chemin du fichier PDF
        $pdfFileName = "product_{$product->getId()}.pdf";
        $pdfPath = $this->pdfDirectory . '/' . $pdfFileName;

        // Sauvegarde du PDF
        file_put_contents($pdfPath, $dompdf->output());

        return $pdfPath;
    }
}
