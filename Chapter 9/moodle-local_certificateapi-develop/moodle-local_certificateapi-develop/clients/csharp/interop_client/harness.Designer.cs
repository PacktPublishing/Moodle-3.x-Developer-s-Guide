namespace interop_client
{
    partial class harness
    {
        /// <summary>
        /// Required designer variable.
        /// </summary>
        private System.ComponentModel.IContainer components = null;

        /// <summary>
        /// Clean up any resources being used.
        /// </summary>
        /// <param name="disposing">true if managed resources should be disposed; otherwise, false.</param>
        protected override void Dispose(bool disposing)
        {
            if (disposing && (components != null))
            {
                components.Dispose();
            }
            base.Dispose(disposing);
        }

        #region Windows Form Designer generated code

        /// <summary>
        /// Required method for Designer support - do not modify
        /// the contents of this method with the code editor.
        /// </summary>
        private void InitializeComponent()
        {
            this.initBtn = new System.Windows.Forms.Button();
            this.startTxt = new System.Windows.Forms.TextBox();
            this.endTxt = new System.Windows.Forms.TextBox();
            this.emailTxt = new System.Windows.Forms.TextBox();
            this.startLbl = new System.Windows.Forms.Label();
            this.endLbl = new System.Windows.Forms.Label();
            this.learnerLbl = new System.Windows.Forms.Label();
            this.ResponseLbl = new System.Windows.Forms.Label();
            this.SuspendLayout();
            // 
            // initBtn
            // 
            this.initBtn.Location = new System.Drawing.Point(259, 94);
            this.initBtn.Margin = new System.Windows.Forms.Padding(4);
            this.initBtn.Name = "initBtn";
            this.initBtn.Size = new System.Drawing.Size(100, 28);
            this.initBtn.TabIndex = 0;
            this.initBtn.Text = "Get Data";
            this.initBtn.UseVisualStyleBackColor = true;
            this.initBtn.Click += new System.EventHandler(this.initBtn_Click);
            // 
            // startTxt
            // 
            this.startTxt.Location = new System.Drawing.Point(16, 39);
            this.startTxt.Margin = new System.Windows.Forms.Padding(4);
            this.startTxt.Name = "startTxt";
            this.startTxt.Size = new System.Drawing.Size(132, 22);
            this.startTxt.TabIndex = 1;
            this.startTxt.Text = "1970-01-01";
            // 
            // endTxt
            // 
            this.endTxt.Location = new System.Drawing.Point(180, 39);
            this.endTxt.Margin = new System.Windows.Forms.Padding(4);
            this.endTxt.Name = "endTxt";
            this.endTxt.Size = new System.Drawing.Size(132, 22);
            this.endTxt.TabIndex = 2;
            this.endTxt.Text = "2017-05-25";
            // 
            // emailTxt
            // 
            this.emailTxt.Location = new System.Drawing.Point(16, 97);
            this.emailTxt.Margin = new System.Windows.Forms.Padding(4);
            this.emailTxt.Name = "emailTxt";
            this.emailTxt.Size = new System.Drawing.Size(219, 22);
            this.emailTxt.TabIndex = 3;
            this.emailTxt.Text = "learner@example.com";
            // 
            // startLbl
            // 
            this.startLbl.AutoSize = true;
            this.startLbl.Location = new System.Drawing.Point(12, 20);
            this.startLbl.Margin = new System.Windows.Forms.Padding(4, 0, 4, 0);
            this.startLbl.Name = "startLbl";
            this.startLbl.Size = new System.Drawing.Size(70, 17);
            this.startLbl.TabIndex = 4;
            this.startLbl.Text = "Start date";
            // 
            // endLbl
            // 
            this.endLbl.AutoSize = true;
            this.endLbl.Location = new System.Drawing.Point(176, 20);
            this.endLbl.Margin = new System.Windows.Forms.Padding(4, 0, 4, 0);
            this.endLbl.Name = "endLbl";
            this.endLbl.Size = new System.Drawing.Size(65, 17);
            this.endLbl.TabIndex = 5;
            this.endLbl.Text = "End date";
            // 
            // learnerLbl
            // 
            this.learnerLbl.AutoSize = true;
            this.learnerLbl.Location = new System.Drawing.Point(16, 78);
            this.learnerLbl.Margin = new System.Windows.Forms.Padding(4, 0, 4, 0);
            this.learnerLbl.Name = "learnerLbl";
            this.learnerLbl.Size = new System.Drawing.Size(95, 17);
            this.learnerLbl.TabIndex = 6;
            this.learnerLbl.Text = "Learner email";
            // 
            // ResponseLbl
            // 
            this.ResponseLbl.AutoSize = true;
            this.ResponseLbl.Location = new System.Drawing.Point(12, 140);
            this.ResponseLbl.Margin = new System.Windows.Forms.Padding(4, 0, 4, 0);
            this.ResponseLbl.Name = "ResponseLbl";
            this.ResponseLbl.Size = new System.Drawing.Size(72, 17);
            this.ResponseLbl.TabIndex = 7;
            this.ResponseLbl.Text = "Response";
            // 
            // harness
            // 
            this.AutoScaleDimensions = new System.Drawing.SizeF(8F, 16F);
            this.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font;
            this.ClientSize = new System.Drawing.Size(379, 322);
            this.Controls.Add(this.ResponseLbl);
            this.Controls.Add(this.learnerLbl);
            this.Controls.Add(this.endLbl);
            this.Controls.Add(this.startLbl);
            this.Controls.Add(this.emailTxt);
            this.Controls.Add(this.endTxt);
            this.Controls.Add(this.startTxt);
            this.Controls.Add(this.initBtn);
            this.Margin = new System.Windows.Forms.Padding(4);
            this.Name = "harness";
            this.Text = "Form1";
            this.ResumeLayout(false);
            this.PerformLayout();

        }

        #endregion

        private System.Windows.Forms.Button initBtn;
        private System.Windows.Forms.TextBox startTxt;
        private System.Windows.Forms.TextBox endTxt;
        private System.Windows.Forms.TextBox emailTxt;
        private System.Windows.Forms.Label startLbl;
        private System.Windows.Forms.Label endLbl;
        private System.Windows.Forms.Label learnerLbl;
        private System.Windows.Forms.Label ResponseLbl;
    }
}

