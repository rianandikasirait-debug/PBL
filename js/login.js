// // script.js

// (function() {
//     const savedData = localStorage.getItem("userData");
    
//     // Inisialisasi admin default jika LocalStorage kosong
//     if (!savedData) {
//         console.log("LocalStorage kosong. Membuat admin default...");

//         const adminDefault = {
//             name: "Admin",
//             email: "admin@gmail.com",
//             password: "admin123",
//             role: "admin"
//         };
//         let users = [adminDefault];

//         localStorage.setItem("userData", JSON.stringify(users));
//     }
// })();

// const loginform = document.getElementById("loginform");

// loginform.addEventListener("submit", function (event) {
//     event.preventDefault();

//     const email = document.getElementById("email").value.trim();
//     const password = document.getElementById("password").value.trim();


//     if (!email || !password) {
//         alert("Mohon isi semua kolom sebelum login!");
//         return;
//     }

//     const savedData = localStorage.getItem("userData");

//     if (!savedData) {
//         alert("Belum ada akun terdaftar! Silakan daftar terlebih dahulu.");
//         window.location.href = "register.html";
//         return;
//     }

//     const users = JSON.parse(savedData);

//     const userData = users.find(user => user.email === email && user.password === password);

//     // 3. Cek apakah pengguna ditemukan
//     if (userData) {
//         alert("Login berhasil!");

//         if (userData.role === "admin") {
//             const adminData = {
//                 name: userData.name,
//                 email: userData.email,
//                 role: "admin"
//             }
//             // Menggunakan kunci 'adminData' (huruf kecil) secara konsisten
//             localStorage.setItem("adminData", JSON.stringify(adminData)); 
//             window.location.href = "admin/dashboard_admin.html"; 

//         } else if (userData.role === "peserta") {
//             const pesertaData = {
//                 name: userData.name,
//                 email: userData.email,
//                 role: "peserta"
//             }
//             localStorage.setItem("pesertaData", JSON.stringify(pesertaData));
//             window.location.href = "peserta/dashboard_peserta.html"; 

//         } else {
//             alert("Role pengguna tidak dikenali!");
//         }
//     } else {
//         // Jika tidak ditemukan di dalam array
//         alert("Email atau password salah!");
//     }
// });