/******************************************************************************
 * Warren's Abstract Machine  -  Implementation by Stefan Buettcher
 *
 * developed:   December 2001 until February 2002
 *
 * QueryCompiler.java contains the QueryCompiler class, a child-class of
 * the Compiler class. QueryCompiler compiles user-written queries into WAM
 * code for execution.
 ******************************************************************************/

import java.util.*;

public class QueryCompiler extends Compiler {

  public QueryCompiler(WAM anOwner) {
    owner = anOwner;
    errorString = "";
    varPrefix = "Q";
  } // end of end of QueryCompiler.QueryCompiler(WAM)

  private boolean query(Vector prog, CompilerStructure struc) {
    Vector oldProg = (Vector)prog.clone();
    struc.type = struc.QUERY;
    struc.head = new CompilerStructure();
    struc.tail = new CompilerStructure();
    if ((body(prog, struc.tail)) && (token(prog, "."))) {
      struc.head.type = struc.HEAD;
      struc.head.tail = null;
      struc.head.head = new CompilerStructure();
      struc.head.head.type = struc.PREDICATE;
      struc.head.head.value = "query$~1/1";
      struc.head.tail = null;
      return true;
    }
    prog.clear();
    prog.addAll(oldProg);
    return false;
  } // end of QueryCompiler.query(Vector, CompilerStructure)

  public Program compile(String aQuery) {
    Vector queryList = stringToList(aQuery);
    CompilerStructure struc = new CompilerStructure();
    errorString = "";
    owner.debug("List:      " + queryList, 2);
    if (query(queryList, struc)) {
      if (queryList.size() > 0) {
        if (errorString.length() > 0)
          owner.writeLn(errorString);
        return null;
      }
      owner.debug("Structure: " + struc.toString(), 2);
      substitutionList = new Vector();
      return structureToCode(struc);
    }
    if (errorString.length() > 0)
      owner.writeLn(errorString);
    return null;
  } // end of QueryCompiler.compile(String)

} // end of class QueryCompiler
