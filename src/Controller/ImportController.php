<?php

namespace App\Controller;

use App\Service\CsvUploadService;
use App\Service\ImportCsvService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ImportController extends AbstractController
{
    #[Route('/import', name: 'app_import')]
    public function index(Request $request, CsvUploadService $csvUploadService, ImportCsvService $importService): Response
    {
        $result = null;
        $error = null;

        if ($request->isMethod('POST')) {
            $tempPath = null;
            try {
                $tempPath = $csvUploadService->validateAndGetPath($request->files->get('csv_file'));
                $result = $importService->import($tempPath);
            } catch (\InvalidArgumentException $e) {
                $error = $e->getMessage();
            } catch (\Exception $e) {
                $error = 'Erreur lors de l\'import : ' . $e->getMessage();
            } finally {
                if ($tempPath !== null && is_file($tempPath)) {
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
