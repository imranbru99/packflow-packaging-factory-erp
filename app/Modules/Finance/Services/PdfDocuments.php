<?php

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\Invoice;
use App\Modules\Production\Models\ProductionOrder;
use App\Modules\Settings\Models\CompanySetting;
use ImranDev\UnicodePdf\Facades\UnicodePdf;
use Symfony\Component\HttpFoundation\Response;

class PdfDocuments
{
    public function invoice(Invoice $invoice): Response
    {
        $invoice->load('customer', 'lines');
        $company = CompanySetting::current();

        return UnicodePdf::view('pdf.invoice', [
            'invoice' => $invoice,
            'company' => $company,
        ])
            ->preset('bengali')
            ->locale('bn')
            ->paper('a4')
            ->title($invoice->number)
            ->name($invoice->number.'.pdf')
            ->download($invoice->number.'.pdf');
    }

    public function jobCard(ProductionOrder $job): Response
    {
        $job->load('product', 'stages.workCenter', 'requirements.material', 'salesOrder.customer');

        return UnicodePdf::view('pdf.job-card', [
            'job' => $job,
            'company' => CompanySetting::current(),
        ])
            ->preset('bengali')
            ->locale('en')
            ->paper('a4')
            ->title($job->number)
            ->download($job->number.'.pdf');
    }
}
