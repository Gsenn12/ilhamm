function openAddTaskModal() {
    new bootstrap.Modal(document.getElementById('addTaskModal')).show();
}

function editTask(id, task, deadline) {
    document.getElementById('editId').value = id;
    document.getElementById('editTask').value = task;
    document.getElementById('editDeadline').value = deadline;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function updateTaskStatus(id, isCompleted) {
    const status = isCompleted ? 'completed' : 'pending';
    window.location.href = `?status=${status}&id=${id}`;
}