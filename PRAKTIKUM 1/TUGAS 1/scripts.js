let count = 1; // To keep track of the row number

function updateTracking(event) {
    event.preventDefault();

    // 1. Get values from form
    const petugas = document.getElementById('petugas').value;
    const lokasi = document.getElementById('lokasi').value;
    const jumlah = document.getElementById('jumlah').value;

    // 2. Target the empty table body
    const tableBody = document.getElementById('table-body');

    // 3. Create a new row (tr)
    const newRow = document.createElement('tr');

    // 4. Fill the row with data cells (td)
    newRow.innerHTML = `
        <td>${count}</td>
        <td>${petugas}</td>
        <td>${lokasi}</td>
        <td>${jumlah} Orang</td>
    `;

    // 5. Add the row to the table
    tableBody.appendChild(newRow);

    // 6. Final touches
    alert("Data untuk " + lokasi + " berhasil ditambahkan ke tabel!");
    count++; // Increment number for next entry
    document.getElementById('trackingForm').reset();
}