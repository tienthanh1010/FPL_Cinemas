<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Đặt vé xem phim</title>

<style>
    body {
        font-family: Arial;
        background: #111;
        color: white;
        text-align: center;
    }

    h2 { margin-top: 20px; }

    .movies button {
        margin: 10px;
        padding: 10px 20px;
        background: red;
        border: none;
        color: white;
        cursor: pointer;
    }

    .showtimes button {
        margin: 5px;
        padding: 8px 15px;
        background: orange;
        border: none;
        cursor: pointer;
    }

    .seats {
        display: grid;
        grid-template-columns: repeat(8, 40px);
        gap: 10px;
        justify-content: center;
        margin-top: 20px;
    }

    .seat {
        width: 40px;
        height: 40px;
        background: #444;
        cursor: pointer;
        line-height: 40px;
    }

    .seat.selected {
        background: green;
    }

    .seat.booked {
        background: gray;
        cursor: not-allowed;
    }

    .summary {
        margin-top: 20px;
    }

    button.confirm {
        padding: 10px 20px;
        background: green;
        border: none;
        color: white;
        margin-top: 10px;
    }
</style>
</head>

<body>

<h2>🎬 Chọn phim</h2>
<div class="movies">
    <button onclick="selectMovie('Avengers')">Avengers</button>
    <button onclick="selectMovie('Spider-Man')">Spider-Man</button>
</div>

<h2>⏰ Suất chiếu</h2>
<div class="showtimes"></div>

<h2>💺 Chọn ghế</h2>
<div class="seats"></div>

<div class="summary"></div>

<script>
    let selectedMovie = "";
    let selectedTime = "";
    let selectedSeats = [];

    function selectMovie(movie) {
        selectedMovie = movie;
        document.querySelector('.showtimes').innerHTML = `
            <button onclick="selectTime('18:00')">18:00</button>
            <button onclick="selectTime('20:00')">20:00</button>
        `;
    }

    function selectTime(time) {
        selectedTime = time;
        renderSeats();
    }

    function renderSeats() {
        const seatContainer = document.querySelector('.seats');
        seatContainer.innerHTML = "";

        for (let i = 1; i <= 40; i++) {
            const seat = document.createElement("div");
            seat.classList.add("seat");
            seat.innerText = i;

            // giả lập ghế đã đặt
            if (i % 7 === 0) {
                seat.classList.add("booked");
            }

            seat.onclick = () => toggleSeat(seat, i);
            seatContainer.appendChild(seat);
        }
    }

    function toggleSeat(element, seatNumber) {
        if (element.classList.contains("booked")) return;

        element.classList.toggle("selected");

        if (selectedSeats.includes(seatNumber)) {
            selectedSeats = selectedSeats.filter(s => s !== seatNumber);
        } else {
            selectedSeats.push(seatNumber);
        }

        updateSummary();
    }

    function updateSummary() {
        document.querySelector('.summary').innerHTML = `
            <p>Phim: ${selectedMovie}</p>
            <p>Giờ: ${selectedTime}</p>
            <p>Ghế: ${selectedSeats.join(", ")}</p>
            <button class="confirm" onclick="confirmBooking()">Xác nhận</button>
        `;
    }

    function confirmBooking() {
        alert(`Đặt vé thành công!\nPhim: ${selectedMovie}\nGhế: ${selectedSeats.join(", ")}`);
    }
</script>

</body>
</html>