class Karyawan:
    def __init__(self, nik, nama, jabatan):
        self.nik = nik
        self.nama = nama
        self.jabatan = jabatan

    def __str__(self):
        return f"| {self.nik:<8} | {self.nama:<20} | {self.jabatan:<15} |"


class SistemKaryawan:
    def __init__(self):
        self.database_karyawan = []

    def tambah_karyawan(self, karyawan):
        # Cek apakah NIK sudah ada (tidak duplikat)
        for k in self.database_karyawan:
            if k.nik == karyawan.nik:
                print(f"❌ NIK {karyawan.nik} sudah terdaftar! Tidak bisa ditambahkan.")
                return
        self.database_karyawan.append(karyawan)
        print("✅ Karyawan berhasil ditambahkan!")

    def cari_karyawan(self, nik):
        for k in self.database_karyawan:
            if k.nik == nik:
                return k
        return None

    def tampilkan_semua(self):
        if not self.database_karyawan:
            print("  (Belum ada data karyawan)")
            return
        print("+----------+----------------------+-----------------+")
        print("| NIK      | Nama                 | Jabatan         |")
        print("+----------+----------------------+-----------------+")
        for k in self.database_karyawan:
            print(k)
        print("+----------+----------------------+-----------------+")


# ── Program Utama ─────────────────────────────────────────────────────────────
sistem = SistemKaryawan()

while True:
    print("\n1. Tambah Karyawan")
    print("2. Cari Karyawan (NIK)")
    print("3. Lihat Semua")
    print("4. Keluar")
    pilihan = input("Pilihan: ")

    if pilihan == "1":
        nik     = input("Masukkan NIK: ")
        nama    = input("Masukkan Nama: ")
        jabatan = input("Masukkan Jabatan: ")
        sistem.tambah_karyawan(Karyawan(nik, nama, jabatan))

    elif pilihan == "2":
        nik = input("Masukkan NIK yang dicari: ")
        hasil = sistem.cari_karyawan(nik)
        if hasil:
            print("\nKaryawan ditemukan:")
            print("+----------+----------------------+-----------------+")
            print("| NIK      | Nama                 | Jabatan         |")
            print("+----------+----------------------+-----------------+")
            print(hasil)
            print("+----------+----------------------+-----------------+")
        else:
            print(f"❌ Karyawan dengan NIK {nik} tidak ditemukan.")

    elif pilihan == "3":
        print("\n=== Daftar Semua Karyawan ===")
        sistem.tampilkan_semua()

    elif pilihan == "4":
        print("Keluar dari program. Sampai jumpa!")
        break

    else:
        print("Pilihan tidak valid. Silakan coba lagi.")