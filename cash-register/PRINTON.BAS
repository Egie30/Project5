DIM Filename AS STRING

FF = FREEFILE
DO
   SHELL ("DIR *.TXT /ON /B >LIST.TXT")
   OPEN "LIST.TXT" FOR INPUT AS #FF
   DO WHILE NOT EOF(FF)
      INPUT #FF, Filename
      IF Filename <> "LIST.TXT" THEN
         IF Filename = "0KILL.TXT" THEN
            PRINT "Printing queue terminated"
            CLOSE FF
            KILL "LIST.TXT"
            KILL "0KILL.TXT"
            END
         END IF
         SHELL ("type " + Filename + ">LPT2")
         PRINT "Printing " + Filename
         SLEEP 1
         PRINT "Print job finished"
         KILL Filename
      END IF
   LOOP
   PRINT "Idling for 2 seconds"
   SLEEP 2
   CLOSE FF
   KILL "LIST.TXT"
LOOP

