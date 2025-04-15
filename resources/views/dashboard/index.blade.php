@extends('layout.template')

@section('title', 'Dashboard')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row">
    <div class="col-xxl-8 mb-6 order-0">
      <div class="card">
        <div class="d-flex align-items-start row">
          <div class="col-sm-7">
            <div class="card-body">
              <h5 class="card-title text-primary mb-3">PEMANTAPAN UJIKOM</h5>
      

             
            </div>
          </div>
          <div class="col-sm-5 text-center text-sm-left">
            <div class="card-body pb-0 px-0 px-md-6">
              <img src="{{asset('admin/assets/img/illustrations/man-with-laptop.png')}}" height="175" class="scaleX-n1-rtl" alt="View Badge User" />
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xxl-4 col-lg-12 col-md-4 order-1">
      <div class="row">
        <div class="col-lg-6 col-md-12 col-6 mb-6">
          <div class="card h-100">
            <div class="card-body pb-4">
              <span class="d-block fw-medium mb-1">Order</span>
              <h4 class="card-title mb-0">276k</h4>
            </div>
            <div id="orderChart" class="pb-3 pe-1"></div>
          </div>
        </div>
        <div class="col-lg-6 col-md-12 col-6 mb-6">
          <div class="card h-100">
            <div class="card-body">
              <div class="card-title d-flex align-items-start justify-content-between mb-4">
                <div class="avatar flex-shrink-0">
                  <img src="{{asset('admin/assets/img/icons/unicons/wallet-info.png')}}" alt="wallet info" class="rounded" />
                </div>
                <div class="dropdown">
                  <button class="btn p-0" type="button" id="cardOpt6" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="icon-base bx bx-dots-vertical-rounded text-body-secondary"></i>
                  </button>
                  <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt6">
                    <a class="dropdown-item" href="javascript:void(0);">View More</a>
                    <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                  </div>
                </div>
              </div>
              <p class="mb-1">Sales</p>
              <h4 class="card-title mb-3">$4,679</h4>
              <small class="text-success fw-medium"><i class="icon-base bx bx-up-arrow-alt"></i> +28.42%</small>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Total Revenue -->
    <div class="col-12 col-xxl-8 order-2 order-md-3 order-xxl-2 mb-6">
      <div class="card">
        <div class="row row-bordered g-0">
          <div class="col-lg-8">
            <div class="card-header d-flex align-items-center justify-content-between">
              <div class="card-title mb-0">
                <h5 class="m-0 me-2">Total Revenue</h5>
              </div>
              <div class="dropdown">
                <button class="btn p-0" type="button" id="totalRevenue" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="icon-base bx bx-dots-vertical-rounded icon-lg text-body-secondary"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="totalRevenue">
                  <a class="dropdown-item" href="javascript:void(0);">Select All</a>
                  <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
                  <a class="dropdown-item" href="javascript:void(0);">Share</a>
                </div>
              </div>
            </div>
            <div id="totalRevenueChart" class="px-3"></div>
          </div>
          <div class="col-lg-4">
            <div class="card-body px-xl-9 py-12 d-flex align-items-center flex-column">
              <div class="text-center mb-6">
                <div class="btn-group">
                  <button type="button" class="btn btn-label-primary">
                    <script>
                      document.write(new Date().getFullYear() - 1);
                    </script>
                  </button>
                  <button type="button" class="btn btn-label-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="visually-hidden">Toggle Dropdown</span>
                  </button>
                  <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="javascript:void(0);">2021</a></li>
                    <li><a class="dropdown-item" href="javascript:void(0);">2020</a></li>
                    <li><a class="dropdown-item" href="javascript:void(0);">2019</a></li>
                  </ul>
                </div>
              </div>

              <div id="growthChart"></div>
              <div class="text-center fw-medium my-6">62% Company Growth</div>

              <div class="d-flex gap-11 justify-content-between">
                <div class="d-flex">
                  <div class="avatar me-2">
                    <span class="avatar-initial rounded-2 bg-label-primary"><i class="icon-base bx bx-dollar icon-lg text-primary"></i></span>
                  </div>
                  <div class="d-flex flex-column">
                    <small>
                      <script>
                        document.write(new Date().getFullYear() - 1);
                      </script>
                    </small>
                    <h6 class="mb-0">$32.5k</h6>
                  </div>
                </div>
                <div class="d-flex">
                  <div class="avatar me-2">
                    <span class="avatar-initial rounded-2 bg-label-info"><i class="icon-base bx bx-wallet icon-lg text-info"></i></span>
                  </div>
                  <div class="d-flex flex-column">
                    <small>
                      <script>
                        document.write(new Date().getFullYear() - 2);
                      </script>
                    </small>
                    <h6 class="mb-0">$41.2k</h6>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--/ Total Revenue -->
    <div class="col-12 col-md-8 col-lg-12 col-xxl-4 order-3 order-md-2">
      <div class="row">
        <div class="col-6 mb-6">
          <div class="card h-100">
            <div class="card-body">
              <div class="card-title d-flex align-items-start justify-content-between mb-4">
                <div class="avatar flex-shrink-0">
                  <img src="{{asset('admin/assets/img/icons/unicons/paypal.png')}}" alt="paypal" class="rounded" />
                </div>
                <div class="dropdown">
                  <button class="btn p-0" type="button" id="cardOpt4" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="icon-base bx bx-dots-vertical-rounded text-body-secondary"></i>
                  </button>
                  <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt4">
                    <a class="dropdown-item" href="javascript:void(0);">View More</a>
                    <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                  </div>
                </div>
              </div>
              <p class="mb-1">Payments</p>
              <h4 class="card-title mb-3">$2,456</h4>
              <small class="text-danger fw-medium"><i class="icon-base bx bx-down-arrow-alt"></i> -14.82%</small>
            </div>
          </div>
        </div>
        <div class="col-6 mb-6">
          <div class="card h-100">
            <div class="card-body pb-0">
              <span class="d-block fw-medium mb-1">Revenue</span>
              <h4 class="card-title mb-0 mb-lg-4">425k</h4>
              <div id="revenueChart"></div>
            </div>
          </div>
        </div>
        <div class="col-12 mb-6">
          <div class="card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center flex-sm-row flex-column gap-10 flex-wrap">
                <div class="d-flex flex-sm-column flex-row align-items-start justify-content-between">
                  <div class="card-title mb-6">
                    <h5 class="text-nowrap mb-1">Profile Report</h5>
                    <span class="badge bg-label-warning">YEAR 2022</span>
                  </div>
                  <div class="mt-sm-auto">
                    <span class="text-success text-nowrap fw-medium"><i class="icon-base bx bx-up-arrow-alt"></i> 68.2%</span>
                    <h4 class="mb-0">$84,686k</h4>
                  </div>
                </div>
                <div id="profileReportChart"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
    
</div>
    @push('js')
        
    @endpush
@endSection
