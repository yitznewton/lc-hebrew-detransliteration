The main class of this project converts LC Romanized Hebrew text into native Hebrew script.  Included are encodings for XML/UTF-8 and Innovative Interfaces three-digit OPAC encoding.  Other encodings will require modification of the code.

Also included is a sample script using this class to read records from a "broken" MARC file, and output:
- an XML file with Hebrew fields for proofreading, and
- a tab-delimited text file intended for use with Expect to add Hebrew fields to an ILS via a character-based interface.

Other scripts could be envisioned to directly modify database tables.