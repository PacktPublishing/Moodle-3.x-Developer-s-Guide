using CookComputing.XmlRpc;
using System;
using System.Collections;
using System.Collections.Generic;
using System.Windows.Forms;

[XmlRpcUrl("http://moodle316.localhost/webservice/xmlrpc/server.php?wstoken=bb34847d964227b44c4700cbca40d5e0")]
public interface IGetLearnerCompletionsByEmail : IXmlRpcProxy
{
    [XmlRpcMethod("local_certificateapi_get_certificates_by_email")]

    System.Object local_certificateapi_get_certificates_by_email(string hostid, string email, string starttime, string endtime);
}

namespace interop_client
{
    public partial class harness : Form
    {
        public harness()
        {
            InitializeComponent();
        }

        private void initBtn_Click(object sender, EventArgs e)
        {
            IGetLearnerCompletionsByEmail proxy = XmlRpcProxyGen.Create<IGetLearnerCompletionsByEmail>();

            System.Object myResults = proxy.local_certificateapi_get_certificates_by_email("testing", emailTxt.Text, startTxt.Text, endTxt.Text);

            if (typeof(IDictionary).IsAssignableFrom(myResults.GetType()))
            {
                IDictionary idict = (IDictionary)myResults;

                // put the strings in a dictionary - key/value pair
                Dictionary<string, string> newDict = new Dictionary<string, string>();
                foreach (object key in idict.Keys)
                {
                    newDict.Add(key.ToString(), idict[key].ToString());
                }

                // Decrypt
                string decrypted = platform.open(newDict["envelope"], newDict["data"]);

                // Display on form
                ResponseLbl.Text = decrypted;
            }
        }
    }
}
