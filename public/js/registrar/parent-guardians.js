function editParentGuardian(id, studentId, fatherName, fatherOccupation, fatherContact, motherName, motherOccupation, motherContact, guardianName, guardianRelationship, guardianContact) {
    document.getElementById('edit_id').value = id;
    document.getElementById('editStudentId').value = studentId || '';
    document.getElementById('editFatherName').value = fatherName || '';
    document.getElementById('editFatherOccupation').value = fatherOccupation || '';
    document.getElementById('editFatherContact').value = fatherContact || '';
    document.getElementById('editMotherName').value = motherName || '';
    document.getElementById('editMotherOccupation').value = motherOccupation || '';
    document.getElementById('editMotherContact').value = motherContact || '';
    document.getElementById('editGuardianName').value = guardianName || '';
    document.getElementById('editGuardianRelationship').value = guardianRelationship || '';
    document.getElementById('editGuardianContact').value = guardianContact || '';
}

function viewParentGuardian(studentName, fatherName, fatherOccupation, fatherContact, motherName, motherOccupation, motherContact, guardianName, guardianRelationship, guardianContact) {
    document.getElementById('viewStudentName').value = studentName || '';
    document.getElementById('viewFatherName').value = fatherName || '';
    document.getElementById('viewFatherOccupation').value = fatherOccupation || '';
    document.getElementById('viewFatherContact').value = fatherContact || '';
    document.getElementById('viewMotherName').value = motherName || '';
    document.getElementById('viewMotherOccupation').value = motherOccupation || '';
    document.getElementById('viewMotherContact').value = motherContact || '';
    document.getElementById('viewGuardianName').value = guardianName || '';
    document.getElementById('viewGuardianRelationship').value = guardianRelationship || '';
    document.getElementById('viewGuardianContact').value = guardianContact || '';
}