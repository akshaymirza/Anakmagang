/* Kedayweb – Intern Directory & Shared Attendance Store
 * Single source of truth for "who are the interns" and where each
 * intern's attendance records live in localStorage, so Admin pages
 * and the intern-facing attendance.html stay in sync.
 */
const InternStore = (() => {
  const LIST_KEY = 'internspace-interns-v1';
  const ATT_PREFIX = 'internspace-attendance-v1::';
  const CURRENT_USER_KEY = 'internspace-current-user';
  const CUTOFF_HOUR = 9; // 09:00 = batas tepat waktu, disamakan dengan attendance.html

  const uid = () => (crypto.randomUUID ? crypto.randomUUID() : `${Date.now()}-${Math.random()}`);
  const fmt = d => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;

  /* ---------------- Intern directory ---------------- */
  const seedInterns = () => {
    if (localStorage.getItem(LIST_KEY)) return;
    const interns = [
      { id: uid(), name: 'John Doe', email: 'john@example.com', role: 'Intern', division: 'Frontend Development' },
      { id: uid(), name: 'Jane Smith', email: 'jane@example.com', role: 'Intern', division: 'Backend Development' },
      { id: uid(), name: 'Alex Doe', email: 'alex@example.com', role: 'Intern', division: 'Fullstack Development' }
    ];
    localStorage.setItem(LIST_KEY, JSON.stringify(interns));
  };

  const list = () => { seedInterns(); return JSON.parse(localStorage.getItem(LIST_KEY) || '[]'); };
  const saveList = arr => localStorage.setItem(LIST_KEY, JSON.stringify(arr));
  const getByName = name => list().find(i => i.name === name) || null;

  const addIntern = data => {
    const arr = list();
    const item = { id: uid(), role: 'Intern', division: '', email: '', ...data };
    arr.push(item);
    saveList(arr);
    seedAttendanceFor(item.name); // beri data contoh supaya kalender admin langsung terisi
    return item;
  };

  const updateIntern = (id, data) => saveList(list().map(i => (i.id === id ? { ...i, ...data } : i)));

  const removeIntern = id => {
    const target = list().find(i => i.id === id);
    saveList(list().filter(i => i.id !== id));
    if (target) localStorage.removeItem(attendanceKey(target.name));
  };

  /* ---------------- Per-intern attendance ---------------- */
  const attendanceKey = name => ATT_PREFIX + name;

  const getAttendance = name => {
    try { return JSON.parse(localStorage.getItem(attendanceKey(name)) || '[]'); }
    catch { return []; }
  };

  const saveAttendance = (name, records) => localStorage.setItem(attendanceKey(name), JSON.stringify(records));

  const upsertAttendance = (name, record) => {
    const data = getAttendance(name).filter(r => r.date !== record.date);
    data.push(record);
    saveAttendance(name, data);
  };

  const deleteAttendance = (name, date) => saveAttendance(name, getAttendance(name).filter(r => r.date !== date));

  // Menghasilkan data kehadiran contoh untuk 1 bulan berjalan supaya kalender Admin
  // langsung punya variasi (hadir / telat / tidak masuk) tanpa perlu input manual.
  const seedAttendanceFor = (name) => {
    if (localStorage.getItem(attendanceKey(name))) return;
    const today = new Date();
    const records = [];
    // pola berbeda per-intern (deterministik dari panjang nama) supaya tiap intern terlihat unik
    const seedNum = name.split('').reduce((a, c) => a + c.charCodeAt(0), 0);
    for (let d = 1; d <= today.getDate(); d++) {
      const dateObj = new Date(today.getFullYear(), today.getMonth(), d);
      const dow = dateObj.getDay();
      if (dow === 0 ) continue; // skip weekend
      const roll = (seedNum + d) % 10;
      let rec;
      if (roll <= 6) {
        rec = { date: fmt(dateObj), status: 'present', clockIn: '08:' + String(30 + (d % 25)).padStart(2, '0'), clockOut: '17:00', reason: '' };
      } else if (roll <= 8) {
        rec = { date: fmt(dateObj), status: 'late', clockIn: '09:' + String(10 + (d % 40)).padStart(2, '0'), clockOut: '17:00', reason: 'Terjebak macet di perjalanan' };
      } else {
        rec = { date: fmt(dateObj), status: 'absent', clockIn: '', clockOut: '', reason: 'Izin sakit, sudah konfirmasi ke mentor' };
      }
      records.push(rec);
    }
    saveAttendance(name, records);
  };

  const seedAllAttendance = () => list().forEach(i => seedAttendanceFor(i.name));

  /* Ringkasan kehadiran semua intern pada satu tanggal (dipakai admin-attendance.html) */
  const getDaySummary = (dateStr) => {
    const interns = list();
    const entries = interns.map(i => ({
      intern: i,
      record: getAttendance(i.name).find(r => r.date === dateStr) || null
    }));
    const present = entries.filter(e => e.record?.status === 'present').length;
    const late = entries.filter(e => e.record?.status === 'late').length;
    const absent = entries.filter(e => e.record?.status === 'absent').length;
    const unmarked = entries.filter(e => !e.record).length;
    return { entries, total: interns.length, present, late, absent, unmarked };
  };

  /* ---------------- "Logged in" intern (demo, no real auth) ---------------- */
  const getCurrentUser = () => localStorage.getItem(CURRENT_USER_KEY) || 'Alex Doe';
  const setCurrentUser = name => localStorage.setItem(CURRENT_USER_KEY, name);

  return {
    list, addIntern, updateIntern, removeIntern, getByName,
    attendanceKey, getAttendance, saveAttendance, upsertAttendance, deleteAttendance,
    seedAttendanceFor, seedAllAttendance, getDaySummary,
    getCurrentUser, setCurrentUser,
    CUTOFF_HOUR
  };
})();
