<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Employee;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    public function invoice(Request $request, Order $order)
    {
        $this->authorize('view', $order);

        $order->load(['shop', 'items', 'payments', 'user']);

        $business = Business::firstOrCreate();

        $pdf = Pdf::loadView('pdf.invoice', [
            'order' => $order,
            'business' => $business,
        ]);

        return $pdf->download("invoice-{$order->order_number}.pdf");
    }

    public function measurementSheet(Request $request, Order $order)
    {
        $this->authorize('view', $order);

        $order->load(['items', 'shop']);

        $business = Business::firstOrCreate();

        $pdf = Pdf::loadView('pdf.measurement', [
            'order' => $order,
            'business' => $business,
        ]);

        return $pdf->download("measurements-{$order->order_number}.pdf");
    }

    public function payrollSlip(Request $request, Employee $employee, $payrollId)
    {
        if (!$request->user()->isShopManager()) {
            return $this->error('Unauthorized', 403);
        }

        $payroll = $employee->payrolls()->with('employee')->findOrFail($payrollId);

        $business = Business::firstOrCreate();

        $pdf = Pdf::loadView('pdf.payroll', [
            'payroll' => $payroll,
            'business' => $business,
        ]);

        return $pdf->download("payroll-{$payroll->id}.pdf");
    }
}
