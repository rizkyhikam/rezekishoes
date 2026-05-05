@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <h1 class="fw-bold text-dark display-6 mb-5" style="letter-spacing: -1.5px;">Dashboard Analitik Ekspedisi</h1>

    <div class="row text-center mb-5 bg-white py-5 rounded-4 shadow-sm mx-0 border border-light">
        <div class="col-md-4">
            <div class="text-primary small fw-bold text-uppercase" style="letter-spacing: 1px;">Selesai (OUT)</div>
            <div class="text-muted mb-2 small">Total Pesanan Selesai</div>
            <div class="display-5 text-dark fw-bold">{{ $summary['cetak'] }}</div>
        </div>
        <div class="col-md-4 border-start border-end">
            <div class="text-warning small fw-bold text-uppercase" style="letter-spacing: 1px;">Diproses (IN)</div>
            <div class="text-muted mb-2 small">Sedang Dalam Pengiriman</div>
            <div class="display-5 text-dark fw-bold">{{ $summary['proses'] }}</div>
        </div>
        <div class="col-md-4">
            <div class="text-danger small fw-bold text-uppercase" style="letter-spacing: 1px;">Dibatalkan</div>
            <div class="text-muted mb-2 small">Total Pesanan Gagal</div>
            <div class="display-5 text-dark fw-bold">{{ $summary['cancel'] }}</div>
        </div>
    </div>

    <div class="d-flex align-items-center mb-4">
        <h5 class="fw-bold text-dark mb-0 me-3">Performa Kurir</h5>
        <div class="flex-grow-1 border-bottom"></div>
    </div>

    <div class="row g-4">
        @foreach($detail as $nama => $s)
        <div class="col-md-4">
            <div class="card bg-white text-center py-4 border-0 shadow-sm rounded-4 h-100 border-top border-primary border-4">
                <div class="fw-bold text-dark mb-1 fs-5">{{ $nama }}</div>
                <div class="text-muted small mb-3 text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">
                    <span class="text-warning">IN</span> / <span class="text-primary">OUT</span> / <span class="text-danger">CANCEL</span>
                </div>
                <div class="h3 fw-bold mb-0 text-dark">
                    <span class="text-warning">{{ $s['in'] }}</span> 
                    <span class="text-muted mx-1">/</span> 
                    <span class="text-primary">{{ $s['out'] }}</span> 
                    <span class="text-muted mx-1">/</span> 
                    <span class="text-danger">{{ $s['cancel'] }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<style>
    /* Tambahan dikit biar makin cakep */
    .rounded-4 { border-radius: 1.2rem !important; }
    .display-5 { font-size: 3rem; }
</style>
@endsection