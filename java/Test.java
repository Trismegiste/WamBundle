public class Test {

  public static void main(String args[]) {
    long ms = System.currentTimeMillis();

    String arr[] = new String[20];
    for (int i = 0; i < 20; i++)
      arr[i] = "Y" + i;

    for (int i = 1; i < 100000; i++)
      for (int j = 0; j < 19; j++) {
        String name = arr[j];
        int len = name.length();
        int cnt = 0;
        int index = 0;
        while (++cnt < len)
          index = index * 10 + (name.charAt(cnt) - '0');
      }


    long now = System.currentTimeMillis() - ms;
    System.out.println("Total time elapsed: " + now + " ms.");
  }

}
