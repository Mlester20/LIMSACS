function editDocumentType(id, document_name, is_required, is_active) {
    document.getElementById('editDocumentTypeId').value = id || '';
    document.getElementById('editDocumentName').value = document_name || '';
    document.getElementById('editIsRequired').value = is_required ? '1' : '0';
    document.getElementById('editIsActive').value = is_active ? '1' : '0';
} 