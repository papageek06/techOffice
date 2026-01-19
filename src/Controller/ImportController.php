<?php

namespace App\Controller;

use App\Service\ImportCsvService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ImportController extends AbstractController
{
    #[Route('/import', name: 'app_import')]
    public function index(Request $request, ImportCsvService $importService): Response
    {
        $result = null;
        $error = null;

        if ($request->isMethod('POST')) {
            $uploadedFile = $request->files->get('csv_file');
            
            if (!$uploadedFile) {
                $error = 'Aucun fichier n\'a été téléchargé.';
            } elseif ($uploadedFile->getClientOriginalExtension() !== 'csv') {
                $error = 'Le fichier doit être au format CSV.';
            } else {
                // Sauvegarder temporairement le fichier
                $tempPath = sys_get_temp_dir() . '/' . uniqid('csv_import_') . '.csv';
                $uploadedFile->move(sys_get_temp_dir(), basename($tempPath));
                
                try {
                    $result = $importService->import($tempPath);
                    
                    // Supprimer le fichier temporaire
                    @unlink($tempPath);
                } catch (\Exception $e) {
                    $error = 'Erreur lors de l\'import : ' . $e->getMessage();
                    @unlink($tempPath);
                }
            }
        }

        return $this->render('import/index.html.twig', [
            'result' => $result,
            'error' => $error,
        ]);
    }
}
