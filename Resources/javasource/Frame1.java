import java.awt.*;
import java.awt.event.*;

public class Frame1 extends Frame {
  TextArea textArea1 = new TextArea();
  Label label1 = new Label();
  Label label2 = new Label();
  Label label3 = new Label();
  TextField textField1 = new TextField();
  Panel panel1 = new Panel();
  WAM wam;         // the Abstract Machine residing inside the interface

  /**Construct the frame*/
  public Frame1() {
    enableEvents(AWTEvent.WINDOW_EVENT_MASK);
    try {
      jbInit();
    }
    catch(Exception e) {
      e.printStackTrace();
    }
  }
  /**Component initialization*/
  private void jbInit() throws Exception  {
    wam = new WAM(new Program());
    wam.GUImode = 1;
    wam.response = textArea1;
    wam.frame = this;
    /*****/
    textArea1.setFont(new java.awt.Font("Monospaced", 0, 12));
    textArea1.setText("Welcome to our WAM Interface!\n\nPlease type in your query below.\n" +
                      "Compiled WAM code can be loaded into the WAM by typing \"load filename.wam\".\n\n");
    textArea1.setBounds(new Rectangle(10, 50, 580, 300));
    //setIconImage(Toolkit.getDefaultToolkit().createImage(Frame1.class.getResource("[Your Icon]")));
    this.setResizable(false);
    this.setLayout(null);
    this.setSize(new Dimension(600, 440));
    this.setTitle("WAM-Interface");
    label1.setFont(new java.awt.Font("Dialog", 1, 12));
    label1.setText("System Output");
    label1.setBounds(new Rectangle(10, 30, 102, 17));
    label2.setFont(new java.awt.Font("Dialog", 1, 12));
    label2.setText("Query");
    label2.setBounds(new Rectangle(10, 360, 59, 17));
    label3.setText("(type \"set var=value\" for modifying internal params, \"quit\" to exit " +
    "the interface, \"clear\" to clear the output box)");
    label3.setBounds(new Rectangle(10, 400, 587, 17));
    textField1.setBounds(new Rectangle(10, 380, 579, 21));
    textField1.addKeyListener(new java.awt.event.KeyAdapter() {
      public void keyTyped(KeyEvent e) {
        textField1_keyTyped(e);
      }
    });
    panel1.setBounds(new Rectangle(0,0, 600, 440));
    panel1.setLayout(null);
    this.add(panel1, null);
    panel1.add(textArea1, null);
    panel1.add(label1, null);
    panel1.add(label3, null);
    panel1.add(textField1, null);
    panel1.add(label2, null);
  }
  /**Overridden so we can exit when window is closed*/
  protected void processWindowEvent(WindowEvent e) {
    super.processWindowEvent(e);
    if (e.getID() == WindowEvent.WINDOW_CLOSING) {
      System.exit(0);
    }
  }

  void textField1_keyTyped(KeyEvent e) {
    if (e.getKeyChar() == 10) {   // Enter pressed
      if (!wam.runQuery(textField1.getText()))
        System.exit(0);
      textField1.setText("");
    }
  }
}

