<?php

namespace App\Http\Controllers;

use App\Services\SauvegardeApplication;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SauvegardeController extends Controller
{
    public function telecharger(SauvegardeApplication $sauvegarde): BinaryFileResponse
    {
        return response()
            ->download($sauvegarde->exporter(), $sauvegarde->nomArchive())
            ->deleteFileAfterSend();
    }
}
