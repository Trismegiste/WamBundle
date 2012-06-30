/******************************************************************************
 * Warren's Abstract Machine  -  Implementation by Stefan Buettcher
 *
 * developed:   December 2001 until February 2002
 *
 * PrologCompiler.java contains the class PrologCompiler, which transforms
 * a Prolog program (given as a string or by its filename) into an equivalent
 * WAM program.
 ******************************************************************************/

import java.io.*;
import java.util.*;

public class PrologCompiler extends Compiler {

  public PrologCompiler(WAM anOwner) {
    owner = anOwner;
    errorString = "";
    varPrefix = "Y";
  }

  public Program compile(String programCode) {
    long ms = System.currentTimeMillis();
    owner.debug("Program Code:", 2);
    owner.debug(programCode, 2);
    Vector programList = stringToList(programCode);
    owner.debug("Program List:", 2);
    owner.debug("String to List: " + (System.currentTimeMillis() - ms) + " ms.", -1);
    owner.debug(programList.toString(), 2);
    CompilerStructure struc = new CompilerStructure();

    ms = System.currentTimeMillis();
    if ((program(programList, struc)) && (programList.size() == 0)) {
      owner.debug("List to Structure: " + (System.currentTimeMillis() - ms) + " ms.", -1);
      updateNames(struc);
      owner.debug(struc.toString(), 2);
      ms = System.currentTimeMillis();
      Program p = structureToCode(struc);
      owner.debug("Structure to Code: " + (System.currentTimeMillis() - ms) + " ms.", -1);
      return p;
    }
    else {
      if (errorString.length() > 0)
        owner.writeLn(errorString);
      return null;
    }
  } // end of PrologCompiler.compile(String)

  // compileSimpleClause can be used in order to implement assert(...) operations
  public Program compileSimpleClause(String programCode) {
    Vector programList = stringToList(programCode);
    CompilerStructure struc = new CompilerStructure();
    if ((clause(programList, struc)) && (programList.size() == 0)) {
      CompilerStructure program = new CompilerStructure();
      program.type = program.PROGRAM;
      program.head = struc;
      updateNames(program);
      return structureToCode(program);
    }
    else
      return null;
  } // end of PrologCompiler.compileSimpleClause(String)

  public Program compileFile(String fileName) {
    String code = "";
    String dummy;
    try {
      long ms = System.currentTimeMillis();
      long atAll = ms;
      BufferedReader r;
      if (fileName.compareToIgnoreCase("stdin") == 0) {
        owner.writeLn("Please type in your Prolog program. EOF is indicated by \"#\".");
        r = new BufferedReader(new InputStreamReader(System.in));
      }
      else
        r = new BufferedReader(new FileReader(fileName));
      do {
        dummy = r.readLine();
        if (dummy != null) {
          if (dummy.compareTo("#") == 0) break;
          code += " " + dummy;
        }
      } while (dummy != null);
      owner.debug("File Operations: " + (System.currentTimeMillis() - ms) + " ms.", -1);
      Program p = compile(code);
      return p;
    }
    catch (Exception io) {
      owner.writeLn("File \"" + fileName + "\" could not be opened.");
      return null;
    }
  } // end of PrologCompiler.compileFile(String)

  private int getProcedureCount(String name, Vector list) {
    for (int i = 0; i < list.size(); i++)
      if (((KeyValue)list.elementAt(i)).key.compareTo(name) == 0)
        return ((KeyValue)list.elementAt(i)).intValue;
    return 0;
  } // end of PrologCompiler.getProcedureCount(String, Vector)

  private void setProcedureCount(String name, int count, Vector list) {
    for (int i = 0; i < list.size(); i++)
      if (((KeyValue)list.elementAt(i)).key.compareTo(name) == 0) {
        ((KeyValue)list.elementAt(i)).intValue = count;
        return;
      }
    list.addElement(new KeyValue(name, count));
    return;
  } // end of PrologCompiler.setProcedureCount(String, int, Vector)

  private void updateNames(CompilerStructure struc) {
    Vector procedureCount = new Vector();
    CompilerStructure s, proc;
    if ((struc.type == struc.PROGRAM) && (struc.head != null)) {
      s = struc;
      do {
        proc = s.head.head.head;
        int cnt = getProcedureCount(proc.value, procedureCount);
        setProcedureCount(proc.value, ++cnt, procedureCount);
        proc.value = proc.value + '~' + cnt;
        s = s.tail;
      } while (s != null);
    }
    if ((struc.type == struc.PROGRAM) && (struc.head != null)) {
      s = struc;
      do {
        proc = s.head.head.head;
        String pv = proc.value;
        if (pv.indexOf('~') > 0)
          pv = pv.substring(0, pv.indexOf('~'));
        proc.value += "/" + getProcedureCount(pv, procedureCount);
        s = s.tail;
      } while (s != null);
    }
  } // end of PrologCompiler.updateNames(CompilerStructure)

} // end of class PrologCompiler

