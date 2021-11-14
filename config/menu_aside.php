<?php
// Aside menu
return [

  'items' => [
    // Dashboard
    [
      'title' => 'Dashboard',
      'root' => true,
      'icon' => 'media/svg/icons/Design/Layers.svg', // or can be 'flaticon-home' or any flaticon-*
      'page' => 'backend/dashboard',
      'new-tab' => false,
    ],
    [
      'title' => 'Users',
      'root' => true,
      'icon' => 'media/svg/icons/General/User.svg', // or can be 'flaticon-home' or any flaticon-*
      'page' => 'backend/users',
      'new-tab' => false,
    ],
    [
      'title' => 'Activity Log',
      'root' => true,
      'icon' => 'media/svg/icons/Home/Timer.svg', // or can be 'flaticon-home' or any flaticon-*
      'page' => 'backend/activitylog',
      'new-tab' => false,
    ],
    [
      'title' => 'Roles',
      'root' => true,
      'icon' => 'media/svg/icons/General/Clipboard.svg', // or can be 'flaticon-home' or any flaticon-*
      'page' => 'backend/roles',
      'new-tab' => false,
    ],
    [
      'section' => 'Master Data',
      'list' => ['costumers', 'drivers', 'transports', 'expenses', 'routes',
        'cargos', 'typecapacities', 'roadmonies', 'anotherexpedition', 'supplierspareparts',
        'brands', 'categories', 'spareparts', 'mastercoa', 'journals', 'kasbonemployees', 'invoicekasbonemployees'
      ]
    ],
    [
      'title' => 'Operasional',
      'desc' => '',
      'icon' => 'media/svg/icons/Communication/Group.svg',
      'bullet' => 'dot',
      'list' => ['costumers', 'drivers', 'transports', 'expenses', 'routes', 'cargos', 'typecapacities', 'roadmonies', 'anotherexpedition'],
      'root' => true,
      'arrow' => true,
      'submenu' => [
        [
          'title' => 'Master Pelanggan',
          'page' => 'backend/costumers'
        ],
        [
          'title' => 'Master Supir',
          'page' => 'backend/drivers'
        ],
        [
          'title' => 'Master Kendaraan',
          'page' => 'backend/transports'
        ],
        [
          'title' => 'Master Biaya',
          'page' => 'backend/expenses'
        ],
        [
          'title' => 'Master Rute',
          'page' => 'backend/routes'
        ],
        [
          'title' => 'Master Muatan',
          'page' => 'backend/cargos'
        ],
        [
          'title' => 'Master Kapasitas',
          'page' => 'backend/typecapacities'
        ],
        [
          'title' => 'Master Uang Jalan',
          'page' => 'backend/roadmonies'
        ],
        [
          'title' => 'Master LDO',
          'page' => 'backend/anotherexpedition'
        ],
      ]
    ],
    [
      'title' => 'Spare Parts',
      'desc' => '',
      'icon' => 'media/svg/icons/Tools/Tools.svg',
      'bullet' => 'dot',
      'root' => true,
      'list' => ['supplierspareparts', 'brands', 'categories', 'spareparts'],
      'arrow' => true,
      'submenu' => [
        [
          'title' => 'Master Supplier Spare Part',
          'page' => 'backend/supplierspareparts'
        ],
        [
          'title' => 'Master Brand',
          'page' => 'backend/brands'
        ],
        [
          'title' => 'Master Kategori',
          'page' => 'backend/categories'
        ],
        [
          'title' => 'Master Spare Part',
          'page' => 'backend/spareparts'
        ],
      ]
    ],
    [
      'title' => 'Akutansi',
      'desc' => '',
      'icon' => 'media/svg/icons/Home/Book-open.svg',
      'bullet' => 'dot',
      'root' => true,
      'list' => ['mastercoa', 'journals', 'kasbonemployees', 'invoicekasbonemployees', 'banks',
        'employees', 'employeesmaster', 'employeessalary', 'monthlymaster'
      ],
      'arrow' => true,
      'submenu' => [
        [
          'title' => 'Master Akun',
          'page' => 'backend/mastercoa'
        ],
        [
          'title' => 'Master Bank',
          'page' => 'backend/banks'
        ],
        [
          'title' => 'Jurnal Transaksi',
          'page' => 'backend/journals'
        ],
        [
          'title' => 'Kasbon Karyawaan',
          'bullet' => 'dot',
          'root' => true,
          'arrow' => true,
          'list' => ['kasbonemployees', 'invoicekasbonemployees'],
          'submenu' => [
            [
              'title' => 'Kasbon Karyawaan',
              'page' => 'backend/kasbonemployees'
            ],
            [
              'title' => 'Invoice Kasbon Karyawaan',
              'page' => 'backend/invoicekasbonemployees'
            ],
          ]
        ],
        [
          'title' => 'Karyawan',
          'bullet' => 'dot',
          'root' => true,
          'arrow' => true,
          'submenu' => [
            [
              'title' => 'Master Karyawan',
              'page' => 'backend/employees'
            ],
            [
              'title' => 'Master Jenis Gaji',
              'page' => 'backend/employeesmaster'
            ],
            [
              'title' => 'Master Gaji Bulanan',
              'page' => 'backend/employeessalary'
            ],
            [
              'title' => 'Gaji Bulanan',
              'page' => 'backend/monthlymaster'
            ]
          ]
        ],
      ]
    ],
    [
      'section' => 'Transaksi',
    ],
    [
      'title' => 'Job Order',
      'desc' => '',
      'icon' => 'media/svg/icons/Communication/Archive.svg',
      'bullet' => 'dot',
      'root' => true,
      'list' => ['submission', 'joborders', 'salaries', 'paymentldo'],
      'arrow' => true,
      'submenu' => [
        [
          'title' => 'Pengajuan Uang Jalan',
          'page' => 'backend/submission'
        ],
        [
          'title' => 'Job Order',
          'page' => 'backend/joborders'
        ],
        [
          'title' => 'Status Gaji Supir',
          'page' => 'backend/salaries'
        ],
        [
          'title' => 'Status Pembayaran LDO',
          'page' => 'backend/paymentldo'
        ],
      ]
    ],
    [
      'title' => 'Pembelian & Pemakaian',
      'desc' => '',
      'icon' => 'media/svg/icons/Shopping/Cart2.svg',
      'bullet' => 'dot',
      'root' => true,
      'list' => ['invoicepurchases', 'invoicereturpurchases', 'invoiceusageitems', 'invoiceusageitemsoutside', 'completepurchaseorder'],
      'arrow' => true,
      'submenu' => [
        [
          'title' => 'Pembelian Barang',
          'page' => 'backend/invoicepurchases'
        ],
        [
          'title' => 'Pelunasan Pembelian Barang',
          'page' => 'backend/completepurchaseorder'
        ],
        [
          'title' => 'Retur Pembelian',
          'page' => 'backend/invoicereturpurchases'
        ],
        [
          'title' => 'Pemakaian Barang',
          'page' => 'backend/invoiceusageitems'
        ],
        [
          'title' => 'Pembelian Barang Diluar',
          'page' => 'backend/invoiceusageitemsoutside'
        ],
      ]
    ],
    [
      'title' => 'Gudang',
      'desc' => '',
      'icon' => 'media/svg/icons/Shopping/Bag2.svg',
      'bullet' => 'dot',
      'root' => true,
      'list' => ['stocks', 'opnames'],
      'arrow' => true,
      'submenu' => [
        [
          'title' => 'Stok Barang',
          'page' => 'backend/stocks'
        ],
        [
          'title' => 'Stok Opname',
          'page' => 'backend/opnames'
        ],
      ]
    ],
    [
      'title' => 'Pembayaran Operasional',
      'desc' => '',
      'icon' => 'media/svg/icons/Shopping/Wallet2.svg',
      'bullet' => 'dot',
      'root' => true,
      'arrow' => true,
      'list' => ['invoicekasbons', 'invoicesalaries', 'invoicecostumers', 'invoiceldo'],
      'submenu' => [
        [
          'title' => 'Kasbon Supir',
          'page' => 'backend/kasbon'
        ],
        [
          'title' => 'Invoice Gaji Supir',
          'page' => 'backend/invoicesalaries'
        ],
        [
          'title' => 'Invoice Pelanggan',
          'page' => 'backend/invoicecostumers'
        ],
        [
          'title' => 'Invoice LDO',
          'page' => 'backend/invoiceldo'
        ],
      ]
    ],
    [
      'section' => 'Laporan',
    ],
    [
      'title' => 'Laporan',
      'desc' => '',
      'icon' => 'media/svg/icons/Communication/Clipboard-list.svg',
      'bullet' => 'dot',
      'root' => true,
      'arrow' => true,
      'list' => ['ledger', 'finance', 'profitloss', 'ledgeraccounting', 'ledgeroperational', 'ledgersparepart',
        'recapitulation', 'reportrecapsalaries', 'reportrecapjoborders', 'reportrecappurchaseorders',
        'reportrecapreturpurchases', 'reportrecapusageitems', 'reportrecapusageitems', 'reportrecapusageitems', 'reportrecapusageitemoutside', 'reportldonetprofit',
        'reportcostumers', 'reportdrivers', 'reporttransports', 'reportpiutangbelumlunas', 'reportpiutanglunas',
        'reportjoborders', 'reportinvoicecostumers', 'reportinvoiceldo',
        'reportkasbondrivers', 'reportsalarydrivers', 'reportkasbonemployees', 'reportsalaryemployees',
        'reportpurchaseorders', 'reportreturpurchases', 'reportusageitems', 'reportusageitemoutside', 'reportstocks'
      ],
      'submenu' => [
        [
          'title' => 'Laporan Akutansi',
          'bullet' => 'dot',
          'arrow' => true,
          'list' => ['ledger', 'finance', 'profitloss', 'ledgeroperational', 'ledgeraccounting', 'ledgersparepart'],
          'submenu' => [
            [
              'title' => 'Laporan Buku Besar',
              'page' => 'backend/ledger'
            ],
            [
              'title' => 'Laporan Buku Besar Operasional',
              'page' => 'backend/ledgeroperational'
            ],
            [
              'title' => 'Laporan Buku Besar Akunting',
              'page' => 'backend/ledgeraccounting'
            ],
            [
              'title' => 'Laporan Buku Besar Spare Part',
              'page' => 'backend/ledgersparepart'
            ],
            [
              'title' => 'Laporan Keuangan',
              'page' => 'backend/finance'
            ],
            [
              'title' => 'Laporan Laba Rugi',
              'page' => 'backend/profitloss'
            ],
          ]
        ],
        [
          'title' => 'Laporan Rekapitulasi',
          'bullet' => 'dot',
          'arrow' => true,
          'list' => ['recapitulation', 'reportrecapsalaries', 'reportrecapjoborders', 'reportrecappurchaseorders',
            'reportrecapreturpurchases', 'reportrecapusageitems', 'reportrecapusageitems', 'reportrecapusageitems', 'reportrecapusageitemoutside', 'reportcustomerroadmoney', 'reportldonetprofit'],
          'submenu' => [
            [
              'title' => 'Laporan Rekapitulasi',
              'page' => 'backend/recapitulation'
            ],
            [
              'title' => 'Laporan Rekap Gaji Supir',
              'page' => 'backend/reportrecapsalaries'
            ],
            [
              'title' => 'Laporan Rekap Tagihan Job Order',
              'page' => 'backend/reportrecapjoborders'
            ],
            [
              'title' => 'Laporan Rekap Purchase Order',
              'page' => 'backend/reportrecappurchaseorders'
            ],
            [
              'title' => 'Laporan Rekap Retur Purchase Order',
              'page' => 'backend/reportrecapreturpurchases'
            ],
            [
              'title' => 'Laporan Rekap Pemakaian Barang',
              'page' => 'backend/reportrecapusageitems'
            ],
            [
              'title' => 'Laporan Rekap Pembelian Barang Diluar',
              'page' => 'backend/reportrecapusageitemoutside'
            ],
            [
              'title' => 'Laporan Rekap Laba LDO',
              'page' => 'backend/reportldonetprofit'
            ],
          ]
        ],
        [
          'title' => 'Laporan Data',
          'bullet' => 'dot',
          'arrow' => true,
          'list' => ['reportcostumers', 'reportdrivers', 'reporttransports', 'reportcustomerroadmoney', 'reportpiutangbelumlunas', 'reportpiutanglunas'],
          'submenu' => [
            ['title' => 'Laporan Data Pelanggan',
              'page' => 'backend/reportcostumers'
            ],
            ['title' => 'Laporan Data Uang Jalan Pelanggan',
              'page' => 'backend/reportcustomerroadmoney'
            ],
            [
              'title' => 'Laporan Data Supir',
              'page' => 'backend/reportdrivers'
            ],
            [
              'title' => 'Laporan Data Kendaraan',
              'page' => 'backend/reporttransports'
            ],
            [
              'title' => 'Laporan Piutang Belum Lunas',
              'page' => 'backend/reportpiutangbelumlunas'
            ],
            [
              'title' => 'Laporan Piutang Lunas',
              'page' => 'backend/reportpiutanglunas'
            ],
          ]
        ],
        [
          'title' => 'Laporan Job Order & Invoice',
          'bullet' => 'dot',
          'arrow' => true,
          'list' => ['reportjoborders', 'reportinvoicecostumers', 'reportinvoiceldo'],
          'submenu' => [
            [
              'title' => 'Laporan Tagihan Job Order',
              'page' => 'backend/reportjoborders'
            ],
            [
              'title' => 'Laporan Invoice Costumer & Fee',
              'page' => 'backend/reportinvoicecostumers'
            ],
            [
              'title' => 'Laporan Invoice LDO',
              'page' => 'backend/reportinvoiceldo'
            ],
          ]
        ],
        [
          'title' => 'Laporan Gaji & Hutang',
          'bullet' => 'dot',
          'arrow' => true,
          'list' => ['reportkasbondrivers', 'reportsalarydrivers', 'reportkasbonemployees', 'reportsalaryemployees'],
          'submenu' => [
            [
              'title' => 'Laporan Hutang Supir',
              'page' => 'backend/reportkasbondrivers'
            ],
            [
              'title' => 'Laporan Gaji Supir',
              'page' => 'backend/reportsalarydrivers'
            ],
            [
              'title' => 'Laporan Hutang Karyawaan',
              'page' => 'backend/reportkasbonemployees'
            ],
            [
              'title' => 'Laporan Gaji Karyawaan',
              'page' => 'backend/reportsalaryemployees'
            ],
          ]
        ],
        [
          'title' => 'Laporan Barang',
          'bullet' => 'dot',
          'arrow' => true,
          'list' => ['reportpurchaseorders', 'reportreturpurchases', 'reportusageitems', 'reportusageitemoutside', 'reportstocks'],
          'submenu' => [
            [
              'title' => 'Laporan Purchase Order',
              'page' => 'backend/reportpurchaseorders'
            ],
            [
              'title' => 'Laporan Retur Purchase Order',
              'page' => 'backend/reportreturpurchases'
            ],
            [
              'title' => 'Laporan Pemakaian Barang',
              'page' => 'backend/reportusageitems'
            ],
            [
              'title' => 'Laporan Pembelian Barang Diluar',
              'page' => 'backend/reportusageitemoutside'
            ],
            [
              'title' => 'Laporan Stok Barang',
              'page' => 'backend/reportstocks'
            ],
          ]
        ],
      ]
    ],
    [
      'section' => 'Master Settings',
    ],
    [
      'title' => 'Settings',
      'desc' => '',
      'icon' => 'media/svg/icons/General/Settings-2.svg',
      'bullet' => 'dot',
      'root' => true,
      'arrow' => true,
      'list' => ['settings', 'prefixes', 'configcoa', 'configledger'],
      'submenu' => [
        [
          'title' => 'Master Setting Web',
          'page' => 'backend/settings'
        ],
        [
          'title' => 'Master Prefix',
          'page' => 'backend/prefixes'
        ],
        [
          'title' => 'Master Kerjasama',
          'page' => 'backend/cooperation'
        ],
        [
          'title' => 'Config Akun COA',
          'page' => 'backend/configcoa'
        ],
        [
          'title' => 'Config Buku Besar COA',
          'page' => 'backend/configledger'
        ]
      ]
    ]
  ]
];
