// resources/js/calendar.js
// Sistema de calendario con notas para el dashboard

class CalendarNotes {
    constructor() {
        this.currentYear = new Date().getFullYear();
        this.selectedMonth = null;
        this.selectedDate = null;
        this.monthNotes = {};
        this.selectedPriority = 'normal';

        this.monthNames = [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        ];

        this.dayNames = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

        this.priorityColors = {
            low: '#10b981',
            normal: '#3b82f6',
            important: '#f59e0b',
            urgent: '#ef4444'
        };

        this.init();
    }

    init() {
        // Elementos del DOM
        this.yearView = document.getElementById('calendarYearView');
        this.monthView = document.getElementById('calendarMonthView');
        this.yearSpan = document.getElementById('calendarCurrentYear');
        this.monthTitle = document.getElementById('calendarMonthTitle');
        this.daysGrid = document.getElementById('calendarDaysGrid');
        this.noteModal = document.getElementById('noteModal');

        // Botones
        document.getElementById('calendarPrevYear')?.addEventListener('click', () => this.changeYear(-1));
        document.getElementById('calendarNextYear')?.addEventListener('click', () => this.changeYear(1));
        document.getElementById('calendarBackToYear')?.addEventListener('click', () => this.showYearView());
        document.getElementById('noteModalClose')?.addEventListener('click', () => this.closeModal());
        document.getElementById('noteSaveBtn')?.addEventListener('click', () => this.saveNote());
        document.getElementById('noteDeleteBtn')?.addEventListener('click', () => this.deleteNote());

        // Botones de prioridad
        document.querySelectorAll('.priority-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.querySelectorAll('.priority-btn').forEach(b => b.classList.remove('ring-4', 'ring-white'));
                e.currentTarget.classList.add('ring-4', 'ring-white');
                this.selectedPriority = e.currentTarget.dataset.priority;
            });
        });

        // Cerrar modal al hacer clic fuera
        this.noteModal?.addEventListener('click', (e) => {
            if (e.target === this.noteModal) {
                this.closeModal();
            }
        });

        // Renderizar vista inicial
        this.renderYearView();
    }

    changeYear(delta) {
        this.currentYear += delta;
        this.renderYearView();
    }

    renderYearView() {
        this.yearSpan.textContent = this.currentYear;
        this.yearView.innerHTML = '';

        for (let month = 0; month < 12; month++) {
            const monthCard = this.createMonthCard(month);
            this.yearView.appendChild(monthCard);
        }

        this.monthView.classList.add('hidden');
        this.yearView.classList.remove('hidden');
    }

    createMonthCard(month) {
        const card = document.createElement('div');
        card.className = 'bg-white/80 rounded-xl p-4 cursor-pointer hover:bg-white hover:shadow-lg transition-all border-2 border-indigo-200';

        const title = document.createElement('div');
        title.className = 'font-bold text-center text-gray-800 mb-2';
        title.textContent = this.monthNames[month];

        const preview = document.createElement('div');
        preview.className = 'text-xs text-center text-gray-600';
        preview.textContent = 'Click para ver días';

        card.appendChild(title);
        card.appendChild(preview);

        card.addEventListener('click', () => this.showMonthView(month));

        return card;
    }

    async showMonthView(month) {
        this.selectedMonth = month;
        this.monthTitle.textContent = `${this.monthNames[month]} ${this.currentYear}`;

        // Cargar notas del mes
        await this.loadMonthNotes(this.currentYear, month + 1);

        // Renderizar días
        this.renderMonthDays(month);

        this.yearView.classList.add('hidden');
        this.monthView.classList.remove('hidden');
    }

    renderMonthDays(month) {
        this.daysGrid.innerHTML = '';

        // Agregar encabezados de días
        this.dayNames.forEach(day => {
            const dayHeader = document.createElement('div');
            dayHeader.className = 'text-center font-bold text-gray-700 py-2';
            dayHeader.textContent = day;
            this.daysGrid.appendChild(dayHeader);
        });

        // Calcular primer día del mes
        const firstDay = new Date(this.currentYear, month, 1).getDay();
        const daysInMonth = new Date(this.currentYear, month + 1, 0).getDate();

        // Espacios vacíos antes del primer día
        for (let i = 0; i < firstDay; i++) {
            const empty = document.createElement('div');
            this.daysGrid.appendChild(empty);
        }

        // Renderizar días del mes
        for (let day = 1; day <= daysInMonth; day++) {
            const dayCard = this.createDayCard(day, month);
            this.daysGrid.appendChild(dayCard);
        }
    }

    createDayCard(day, month) {
        const card = document.createElement('div');
        const dateStr = `${this.currentYear}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

        // Verificar si hay nota para este día
        const note = this.monthNotes[dateStr];
        const hasNote = note && note.note;

        let bgColor = 'bg-white/80';
        let borderColor = 'border-gray-200';

        if (hasNote) {
            const color = this.priorityColors[note.priority] || this.priorityColors.normal;
            bgColor = 'bg-white';
            borderColor = 'border-4';
            card.style.borderColor = color;
        }

        // Marcar el día actual
        const today = new Date();
        const isToday = today.getFullYear() === this.currentYear &&
                       today.getMonth() === month &&
                       today.getDate() === day;

        if (isToday) {
            card.classList.add('ring-2', 'ring-blue-500');
        }

        card.className = `${bgColor} rounded-xl p-3 cursor-pointer hover:shadow-lg transition-all border-2 ${borderColor} min-h-[80px] flex flex-col`;

        const dayNum = document.createElement('div');
        dayNum.className = 'font-bold text-gray-800 text-lg';
        dayNum.textContent = day;

        card.appendChild(dayNum);

        if (hasNote) {
            const notePreview = document.createElement('div');
            notePreview.className = 'text-xs text-gray-600 mt-2 line-clamp-2';
            notePreview.textContent = note.note;
            card.appendChild(notePreview);
        }

        card.addEventListener('click', () => this.openDayNote(dateStr));

        return card;
    }

    async openDayNote(dateStr) {
        this.selectedDate = dateStr;

        // Cargar nota si existe
        const note = await this.loadDayNote(dateStr);

        // Actualizar modal
        const dateParts = dateStr.split('-');
        const month = parseInt(dateParts[1]) - 1;
        const day = parseInt(dateParts[2]);

        document.getElementById('noteModalTitle').textContent = `${day} de ${this.monthNames[month]}, ${this.currentYear}`;
        document.getElementById('noteContent').value = note?.note || '';

        // Establecer prioridad
        this.selectedPriority = note?.priority || 'normal';
        document.querySelectorAll('.priority-btn').forEach(btn => {
            btn.classList.remove('ring-4', 'ring-white');
            if (btn.dataset.priority === this.selectedPriority) {
                btn.classList.add('ring-4', 'ring-white');
            }
        });

        // Mostrar última actualización
        const lastUpdatedEl = document.getElementById('noteLastUpdated');
        if (note?.updated_at) {
            lastUpdatedEl.textContent = `Última actualización: ${note.updated_at}`;
        } else {
            lastUpdatedEl.textContent = '';
        }

        // Mostrar/ocultar botón eliminar
        const deleteBtn = document.getElementById('noteDeleteBtn');
        if (note) {
            deleteBtn.classList.remove('hidden');
        } else {
            deleteBtn.classList.add('hidden');
        }

        // Mostrar modal
        this.noteModal.classList.remove('hidden');
    }

    closeModal() {
        this.noteModal.classList.add('hidden');
    }

    async loadMonthNotes(year, month) {
        try {
            const response = await fetch(`/calendar/month/${year}/${month}`);
            const notes = await response.json();

            // Indexar notas por fecha
            this.monthNotes = {};
            notes.forEach(note => {
                this.monthNotes[note.date] = note;
            });
        } catch (error) {
            console.error('Error cargando notas del mes:', error);
            this.monthNotes = {};
        }
    }

    async loadDayNote(date) {
        try {
            const response = await fetch(`/calendar/day/${date}`);
            const note = await response.json();
            return note;
        } catch (error) {
            console.error('Error cargando nota del día:', error);
            return null;
        }
    }

    async saveNote() {
        const noteContent = document.getElementById('noteContent').value;

        if (!noteContent.trim()) {
            alert('Por favor escribe una nota antes de guardar');
            return;
        }

        try {
            const response = await fetch('/calendar/note', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    date: this.selectedDate,
                    note: noteContent,
                    priority: this.selectedPriority
                })
            });

            const result = await response.json();

            if (result.success) {
                // Actualizar cache de notas
                this.monthNotes[this.selectedDate] = result.note;

                // Cerrar modal
                this.closeModal();

                // Refrescar vista del mes
                this.renderMonthDays(this.selectedMonth);

                alert('✅ Nota guardada exitosamente');
            } else {
                alert('❌ Error al guardar la nota');
            }
        } catch (error) {
            console.error('Error guardando nota:', error);
            alert('❌ Error al guardar la nota');
        }
    }

    async deleteNote() {
        if (!confirm('¿Estás seguro de que quieres eliminar esta nota?')) {
            return;
        }

        try {
            const response = await fetch(`/calendar/note/${this.selectedDate}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const result = await response.json();

            if (result.success) {
                // Eliminar de cache
                delete this.monthNotes[this.selectedDate];

                // Cerrar modal
                this.closeModal();

                // Refrescar vista del mes
                this.renderMonthDays(this.selectedMonth);

                alert('✅ Nota eliminada exitosamente');
            } else {
                alert('❌ Error al eliminar la nota');
            }
        } catch (error) {
            console.error('Error eliminando nota:', error);
            alert('❌ Error al eliminar la nota');
        }
    }

    showYearView() {
        this.selectedMonth = null;
        this.renderYearView();
    }
}

// Inicializar calendario cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new CalendarNotes();
    });
} else {
    new CalendarNotes();
}

export default CalendarNotes;
