using System;
using System.Text;
using System.IO;
using Org.BouncyCastle.OpenSsl;
using Org.BouncyCastle.Crypto.Parameters;
using Org.BouncyCastle.Security;

namespace interop_client
{
    class platform
    {
        private static string _gzipDecompress(byte[] gZipBuffer)
        {
            //Prepare for decompress
            using (System.IO.MemoryStream output = new System.IO.MemoryStream())
            {
                using (System.IO.MemoryStream ms = new System.IO.MemoryStream(gZipBuffer))
                using (System.IO.Compression.GZipStream sr = new System.IO.Compression.GZipStream(ms, System.IO.Compression.CompressionMode.Decompress))
                {
                    sr.CopyTo(output);
                }

                string str = Encoding.UTF8.GetString(output.GetBuffer(), 0, (int)output.Length);
                return str;
            }
        }

        public static RsaPrivateCrtKeyParameters GetPrivateKey(String pemFile)
        {
            if (string.IsNullOrEmpty(pemFile))
                throw new ArgumentNullException("pemFile");

            string privateKey = File.Exists(pemFile) ? File.ReadAllText(pemFile) : pemFile;

            var reader = new PemReader(new StringReader(privateKey));

            RsaPrivateCrtKeyParameters privkey = (RsaPrivateCrtKeyParameters)reader.ReadObject();

            return privkey;
        }

        public static string open(string envelope, string data)
        {
            var privateKeyParameters = GetPrivateKey(@"C:\Development\reporting_test_client\key.pem");

            var rsaCipher = CipherUtilities.GetCipher("RSA//PKCS1PADDING");
            rsaCipher.Init(false, privateKeyParameters);
            var keyBytes = rsaCipher.DoFinal(Convert.FromBase64String(envelope));                    /* decrypt key using RSA */

            var rc4CipherDecrypt = CipherUtilities.GetCipher("RC4");
            var decryptParameter = new KeyParameter(keyBytes);
            rc4CipherDecrypt.Init(false, decryptParameter);
            var rc4DataBytesDecrypt = rc4CipherDecrypt.DoFinal(Convert.FromBase64String(data)); /* decrypt data using RC4 */
            var dataString = Encoding.ASCII.GetString(rc4DataBytesDecrypt);
            var decompressed = _gzipDecompress(rc4DataBytesDecrypt);
         
            return decompressed;
        }


    }
}
