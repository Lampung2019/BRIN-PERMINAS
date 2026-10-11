import { Head } from '@inertiajs/react';

export default function MyAttendance() {
    return (
        <>
            <Head title="Rekap Presensi Saya" />

            <div className="p-6">
                <h1 className="text-2xl font-bold">
                    Rekap Presensi Saya
                </h1>

                <div className="mt-4 bg-white rounded shadow p-4">
                    Data presensi pegawai akan tampil di sini.
                </div>
            </div>
        </>
    );
}