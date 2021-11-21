File upload demo application

Steps for the flow:
User uploads file from the ui to the server
Request reaches 
File is moved to uploads directory -- can be moved to azure/aws cloud
Details of file saved in db table
User upload finished

Control handed over to gearman worker for processing the file
In the backgroud gearman processes the uploaded file and dumps info from file into database
To spawn the gearman worker we use supervisor

