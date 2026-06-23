take the @resources/views/registrar/student-records.php and apply it into @resources/views/teachers/students.php but do not add create students in their since it was intended for teacher so you must only apply view and update for the students masters list. and add the dropped and trasferred function to the students.php under the teacher as your reference take these

@resources/views/registrar/enrollment.php of the registrar how transferred and dropped logic works 

and implement this 
protected $auditLogs;
$this->auditLogs = new AuditLogs($con);

to identify teachers activity