<?php

// FITUR SHELL ADMIN (BAGIAN NAVBAR PORTAL) - ORANG 1
function admin_layout(string $adminTitle, string $activeAdmin): array
{
    $currentAdminUser = current_user();
    $view = [];
    $view['navClassDashboard'] = $activeAdmin === 'admin/dashboard.html'
        ? 'bg-orange-400 text-slate-950 font-semibold'
        : 'text-slate-200 hover:bg-white/10';
    $view['navClassBerita'] = $activeAdmin === 'admin/berita.html'
        ? 'bg-orange-400 text-slate-950 font-semibold'
        : 'text-slate-200 hover:bg-white/10';
    $view['is_admin'] = [];
    if (is_admin()) {
        $item1 = [];
        $item1['navClassKategori'] = $activeAdmin === 'admin/kategori.html'
            ? 'bg-orange-400 text-slate-950 font-semibold'
            : 'text-slate-200 hover:bg-white/10';
        $item1['navClassFakultas'] = $activeAdmin === 'admin/fakultas.html'
            ? 'bg-orange-400 text-slate-950 font-semibold'
            : 'text-slate-200 hover:bg-white/10';
        $item1['navClassAkun'] = $activeAdmin === 'admin/akun.html'
            ? 'bg-orange-400 text-slate-950 font-semibold'
            : 'text-slate-200 hover:bg-white/10';
        $view['is_admin'][] = $item1;
    }
    $view['navClassKomentar'] = $activeAdmin === 'admin/komentar.html'
        ? 'bg-orange-400 text-slate-950 font-semibold'
        : 'text-slate-200 hover:bg-white/10';
    $view['is_admin2'] = [];
    if (is_admin()) {
        $item1 = [];
        $item1['navClassPesan'] = $activeAdmin === 'admin/pesan.html'
            ? 'bg-orange-400 text-slate-950 font-semibold'
            : 'text-slate-200 hover:bg-white/10';
        $view['is_admin2'][] = $item1;
    }
    $view['currentAdminUser_name'] = e($currentAdminUser['name'] ?? '');
    $view['strtoupper_currentAdminUser_role'] = e(strtoupper($currentAdminUser['role'] ?? ''));
    $view['facultySuffix'] = !empty($currentAdminUser['faculty_name'])
        ? ' · ' . e($currentAdminUser['faculty_name'])
        : '';
    $view['csrf_token'] = csrf_token();
    $view['adminTitle'] = e($adminTitle);
    $view['currentAdminUser_name2'] = e($currentAdminUser['name'] ?? '');
    $view['date_d_m_Y'] = date('d/m/Y');
    return $view;
}

function public_layout(string $activeNav): array
{
    $facultiesForNav = get_faculties();
    $view = [];
    $view['activeNav_home_active'] = $activeNav==='home'?'active':'';
    $view['activeNav_news_active'] = $activeNav==='news'?'active':'';
    $view['facultiesForNav'] = [];
    foreach ($facultiesForNav as $navFaculty) {
        $item1 = [];
        $item1['facultyUrl'] = e(url('fakultas.html?slug=' . $navFaculty['slug']));
        $item1['navFaculty_code'] = e($navFaculty['code']);
        $item1['navFaculty_name'] = e($navFaculty['name']);
        $view['facultiesForNav'][] = $item1;
    }
    $view['activeNav_about_active'] = $activeNav==='about'?'active':'';
    $view['activeNav_contact_active'] = $activeNav==='contact'?'active':'';
    $view['facultiesForNav2'] = [];
    foreach ($facultiesForNav as $navFaculty) {
        $item1 = [];
        $item1['facultyUrl2'] = e(url('fakultas.html?slug=' . $navFaculty['slug']));
        $item1['navFaculty_code_navFaculty_name'] = e($navFaculty['code'] . ' · ' . $navFaculty['name']);
        $view['facultiesForNav2'][] = $item1;
    }
    $view['date_Y'] = date('Y');
    return $view;
}
