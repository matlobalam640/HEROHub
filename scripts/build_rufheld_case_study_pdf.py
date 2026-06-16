"""
Build client-ready Rufheld case study PDF with embedded images.
Run: python scripts/build_rufheld_case_study_pdf.py
"""
from __future__ import annotations

import sys
from pathlib import Path
from xml.sax.saxutils import escape

import requests
from reportlab.lib import colors
from reportlab.lib.colors import Color
from reportlab.lib.enums import TA_CENTER, TA_JUSTIFY, TA_LEFT
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.lib.units import inch
from reportlab.lib.utils import ImageReader
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.platypus import (
    Image,
    PageBreak,
    Paragraph,
    SimpleDocTemplate,
    Spacer,
    Table,
    TableStyle,
)
from reportlab.platypus.flowables import HRFlowable

IMAGES = [
    ("hero", "https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=2000&q=80"),
    ("challenge", "https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=1600&q=80"),
    ("automation", "https://images.unsplash.com/photo-1551434678-e076c223a692?w=1600&q=80"),
    ("results", "https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=1600&q=80"),
]


def download_images(dest_dir: Path) -> dict[str, Path]:
    dest_dir.mkdir(parents=True, exist_ok=True)
    out: dict[str, Path] = {}
    for name, url in IMAGES:
        path = dest_dir / f"{name}.jpg"
        r = requests.get(url, timeout=90)
        r.raise_for_status()
        path.write_bytes(r.content)
        out[name] = path
    return out


def register_fonts() -> tuple[str, str]:
    arial = Path(r"C:\Windows\Fonts\arial.ttf")
    arial_bd = Path(r"C:\Windows\Fonts\arialbd.ttf")
    if arial.is_file() and arial_bd.is_file():
        pdfmetrics.registerFont(TTFont("CaseArial", str(arial)))
        pdfmetrics.registerFont(TTFont("CaseArial-Bold", str(arial_bd)))
        return "CaseArial", "CaseArial-Bold"
    return "Helvetica", "Helvetica-Bold"


def P(text: str, style: ParagraphStyle) -> Paragraph:
    return Paragraph(escape(text).replace("\n", "<br/>"), style)


def build_styles(font: str, font_bold: str) -> dict[str, ParagraphStyle]:
    base = getSampleStyleSheet()
    return {
        "title": ParagraphStyle(
            name="CSTitle",
            parent=base["Heading1"],
            fontName=font_bold,
            fontSize=22,
            leading=28,
            textColor=colors.HexColor("#0f172a"),
            spaceAfter=14,
            alignment=TA_LEFT,
        ),
        "h1": ParagraphStyle(
            name="CSH1",
            parent=base["Heading2"],
            fontName=font_bold,
            fontSize=14,
            leading=18,
            textColor=colors.HexColor("#0f172a"),
            spaceBefore=16,
            spaceAfter=8,
            alignment=TA_LEFT,
        ),
        "h2": ParagraphStyle(
            name="CSH2",
            parent=base["Heading3"],
            fontName=font_bold,
            fontSize=11.5,
            leading=15,
            textColor=colors.HexColor("#1e293b"),
            spaceBefore=10,
            spaceAfter=6,
            alignment=TA_LEFT,
        ),
        "body": ParagraphStyle(
            name="CSBody",
            parent=base["Normal"],
            fontName=font,
            fontSize=10.5,
            leading=14.5,
            alignment=TA_JUSTIFY,
            textColor=colors.HexColor("#334155"),
            spaceAfter=8,
        ),
        "bullet": ParagraphStyle(
            name="CSBullet",
            parent=base["Normal"],
            fontName=font,
            fontSize=10.5,
            leading=14.5,
            leftIndent=18,
            bulletIndent=8,
            textColor=colors.HexColor("#334155"),
            spaceAfter=5,
        ),
        "meta": ParagraphStyle(
            name="CSMeta",
            parent=base["Normal"],
            fontName=font,
            fontSize=10,
            leading=13,
            textColor=colors.HexColor("#475569"),
            spaceAfter=4,
        ),
        "caption": ParagraphStyle(
            name="CSCaption",
            parent=base["Normal"],
            fontName=font,
            fontSize=9,
            leading=12,
            textColor=colors.HexColor("#64748b"),
            alignment=TA_CENTER,
            spaceAfter=14,
        ),
    }


def cover_canvas_factory(hero_path: Path, font: str, font_bold: str):
    w, h = A4

    def cover(canvas, doc):
        canvas.saveState()
        try:
            canvas.drawImage(
                ImageReader(str(hero_path)),
                0,
                0,
                width=w,
                height=h,
                preserveAspectRatio=True,
                anchor="c",
            )
        except Exception:
            canvas.setFillColor(colors.HexColor("#1e293b"))
            canvas.rect(0, 0, w, h, fill=1, stroke=0)

        canvas.setFillColor(Color(0.06, 0.09, 0.16, alpha=0.62))
        canvas.rect(0, h * 0.32, w, h * 0.38, fill=1, stroke=0)

        canvas.setFillColor(colors.white)
        canvas.setFont(font, 11)
        canvas.drawString(48, h * 0.58, "SUCCESS STORY")

        canvas.setFont(font_bold, 30)
        canvas.drawString(48, h * 0.52, "Rufheld")

        canvas.setFont(font_bold, 15)
        canvas.drawString(48, h * 0.46, "Streamlining Reputation Management")

        canvas.setFont(font, 10)
        canvas.setFillColor(colors.HexColor("#e2e8f0"))
        canvas.drawString(48, h * 0.40, "Xpert Prime Case Study Series")

        canvas.setFont(font, 9)
        canvas.drawString(48, 36, "Germany-focused Google review removal | Pay only on success")
        canvas.restoreState()

    return cover


def later_pages(canvas, doc):
    canvas.saveState()
    canvas.setStrokeColor(colors.HexColor("#e2e8f0"))
    canvas.setLineWidth(0.5)
    w, _ = A4
    canvas.line(36, 28, w - 36, 28)
    canvas.setFillColor(colors.HexColor("#94a3b8"))
    canvas.setFont("Helvetica", 8)
    canvas.drawRightString(w - 40, 18, "Rufheld | Xpert Prime")
    canvas.restoreState()


def main() -> int:
    font, font_bold = register_fonts()
    styles = build_styles(font, font_bold)

    base = Path(__file__).resolve().parent.parent
    img_dir = base / "storage" / "app" / "rufheld_pdf_assets"
    try:
        imgs = download_images(img_dir)
    except Exception as e:
        print("Image download failed:", e, file=sys.stderr)
        return 1

    out = Path.home() / "Downloads" / "Rufheld_Case_Study_Streamlining_Reputation_Management.pdf"
    doc = SimpleDocTemplate(
        str(out),
        pagesize=A4,
        leftMargin=48,
        rightMargin=48,
        topMargin=56,
        bottomMargin=52,
        title="Case Study: Rufheld",
        author="Xpert Prime",
    )

    story: list = []

    # Trigger page 1 (cover drawn in onFirstPage)
    story.append(Spacer(1, 0.1))
    story.append(PageBreak())

    # --- Main title block (page 2) ---
    story.append(P("Case Study: Rufheld — Streamlining Reputation Management", styles["title"]))
    story.append(HRFlowable(width="100%", thickness=1, color=colors.HexColor("#e2e8f0"), spaceBefore=2, spaceAfter=14))
    story.append(Paragraph("<b>Client:</b> Rufheld", styles["meta"]))
    story.append(
        Paragraph("<b>Industry:</b> Digital Services / Online Reputation Management", styles["meta"])
    )
    story.append(Paragraph("<b>Focus:</b> Germany-focused Google review removal", styles["meta"]))
    story.append(Paragraph("<b>Prepared for:</b> Xpert Prime Case Study Series", styles["meta"]))
    story.append(Spacer(1, 10))

    # Two-column feel: text + image
    img_w = 2.35 * inch
    exec_style = ParagraphStyle(
        name="ExecWrap",
        parent=styles["body"],
        fontName=font,
        alignment=TA_JUSTIFY,
        fontSize=10.5,
        leading=14.5,
        textColor=colors.HexColor("#334155"),
    )
    ph = Paragraph(
        "Rufheld specializes in protecting business reputations by identifying and removing unfair or "
        "policy-violating Google reviews. Their <b>pay only on success</b> model ensures clients are "
        "billed only when a case is successfully resolved.<br/><br/>"
        "Before implementation, operations were fragmented across spreadsheets and disconnected tools. "
        "This caused inefficiencies, manual errors, slow payment collection, and weak alignment between "
        "outcomes and billing.<br/><br/>"
        "We implemented a <b>Zoho-powered ecosystem</b> integrating CRM, automation, and Stripe-based billing. "
        "The result was a fully automated lifecycle—from lead to successful case to payment—delivering reduced "
        "manual work, faster revenue collection, improved operational efficiency, and real-time business insights.",
        exec_style,
    )
    img_exec = Image(str(imgs["automation"]), width=img_w, height=img_w * 0.65)
    t = Table([[ph, img_exec]], colWidths=[4.25 * inch, img_w + 8])
    t.setStyle(
        TableStyle(
            [
                ("VALIGN", (0, 0), (-1, -1), "TOP"),
                ("LEFTPADDING", (0, 0), (-1, -1), 0),
                ("RIGHTPADDING", (0, 0), (-1, -1), 0),
            ]
        )
    )
    story.append(P("Executive Summary", styles["h1"]))
    story.append(t)
    story.append(Spacer(1, 16))

    story.append(P("About Rufheld", styles["h1"]))
    story.append(
        P(
            "Rufheld is a Germany-focused specialist in removing unfair or policy-violating Google reviews. "
            "Their service combines expert manual case analysis, fast turnaround times, and results-driven "
            "pricing (pay on success). Their positioning goes beyond removal into complete online reputation "
            "protection and management.",
            styles["body"],
        )
    )
    story.append(Spacer(1, 10))

    story.append(Image(str(imgs["challenge"]), width=6.8 * inch, height=2.05 * inch))
    story.append(P("Operations, process, and alignment challenges", styles["caption"]))

    story.append(P("The Challenge", styles["h1"]))
    ch_data = [
        ["Area", "Problem"],
        ["Operations", "No centralized system; data scattered across tools"],
        ["Process", "Manual tracking increased inefficiency and errors"],
        ["Cash flow", "Payment collection was slow and manual"],
        ["Commercial alignment", "No direct link between case success and billing"],
    ]
    ch = Table(ch_data, colWidths=[1.55 * inch, 4.95 * inch])
    ch.setStyle(
        TableStyle(
            [
                ("BACKGROUND", (0, 0), (-1, 0), colors.HexColor("#0f172a")),
                ("TEXTCOLOR", (0, 0), (-1, 0), colors.white),
                ("FONTNAME", (0, 0), (-1, 0), font_bold),
                ("FONTSIZE", (0, 0), (-1, 0), 10),
                ("BOTTOMPADDING", (0, 0), (-1, 0), 8),
                ("TOPPADDING", (0, 0), (-1, 0), 8),
                ("BACKGROUND", (0, 1), (-1, -1), colors.HexColor("#f8fafc")),
                ("TEXTCOLOR", (0, 1), (-1, -1), colors.HexColor("#334155")),
                ("FONTNAME", (0, 1), (-1, -1), font),
                ("FONTSIZE", (0, 1), (-1, -1), 10),
                ("GRID", (0, 0), (-1, -1), 0.5, colors.HexColor("#e2e8f0")),
                ("VALIGN", (0, 0), (-1, -1), "TOP"),
                ("LEFTPADDING", (0, 0), (-1, -1), 10),
                ("RIGHTPADDING", (0, 0), (-1, -1), 10),
                ("TOPPADDING", (0, 1), (-1, -1), 8),
                ("BOTTOMPADDING", (0, 1), (-1, -1), 8),
            ]
        )
    )
    story.append(ch)
    story.append(
        P(
            "These issues limited scalability, reduced visibility, and risked revenue leakage.",
            styles["body"],
        )
    )
    story.append(Spacer(1, 6))

    story.append(P("Objectives", styles["h1"]))
    for line in [
        "Centralize leads, accounts, contacts, and cases",
        "Standardize the review-case lifecycle",
        "Automate workflows and validations",
        "Enable success-based automated billing via Stripe",
        "Provide real-time dashboards for operations and leadership",
    ]:
        story.append(Paragraph(f"&bull; {escape(line)}", styles["bullet"]))
    story.append(Spacer(1, 10))

    story.append(P("The Solution", styles["h1"]))

    story.append(P("1. Centralized CRM Architecture", styles["h2"]))
    story.append(
        P(
            "Implemented Zoho CRM as the single source of truth with structured modules: Leads, Accounts, "
            "Contacts, and Review Cases as the core module.",
            styles["body"],
        )
    )

    story.append(P("2. Case Lifecycle Management", styles["h2"]))
    story.append(
        P(
            "Defined a structured pipeline: New → Request → Analysis Submitted → In Progress → Successful / Failed. "
            "This ensures clear ownership, standardized workflows, and automation triggers at each stage.",
            styles["body"],
        )
    )

    story.append(P("3. Success-Based Billing Integration", styles["h2"]))
    story.append(
        P(
            "Integrated Stripe for automated billing: payment triggered only after successful case resolution, "
            "fully aligned with Rufheld's business model, with minimal to zero manual billing effort.",
            styles["body"],
        )
    )

    story.append(P("4. Automation & Validation", styles["h2"]))
    story.append(
        P(
            "Deluge for backend logic; client-side scripts for validation and control; automated workflows for "
            "stage transitions.",
            styles["body"],
        )
    )

    story.append(P("5. Real-Time Reporting & Insights", styles["h2"]))
    story.append(
        P(
            "Dashboards via Zoho Analytics for case throughput, success rates, revenue tracking, and operational "
            "performance.",
            styles["body"],
        )
    )

    story.append(P("6. Integrated Ecosystem", styles["h2"]))
    eco = [
        ["Component", "Role"],
        ["Zoho CRM", "Core system & automation"],
        ["Zoho Books", "Financial management"],
        ["Zoho Flow", "Workflow orchestration"],
        ["Zoho Analytics", "Reporting & dashboards"],
        ["Stripe", "Payment automation"],
        ["Deluge", "Backend logic"],
        ["REST APIs & Scripts", "System integrations"],
    ]
    et = Table(eco, colWidths=[2.05 * inch, 4.45 * inch])
    et.setStyle(
        TableStyle(
            [
                ("BACKGROUND", (0, 0), (-1, 0), colors.HexColor("#1e293b")),
                ("TEXTCOLOR", (0, 0), (-1, 0), colors.white),
                ("FONTNAME", (0, 0), (-1, 0), font_bold),
                ("FONTSIZE", (0, 0), (-1, 0), 10),
                ("TOPPADDING", (0, 0), (-1, 0), 8),
                ("BOTTOMPADDING", (0, 0), (-1, 0), 8),
                ("BACKGROUND", (0, 1), (-1, -1), colors.white),
                ("FONTNAME", (0, 1), (-1, -1), font),
                ("FONTSIZE", (0, 1), (-1, -1), 10),
                ("GRID", (0, 0), (-1, -1), 0.5, colors.HexColor("#e2e8f0")),
                ("VALIGN", (0, 0), (-1, -1), "TOP"),
                ("LEFTPADDING", (0, 0), (-1, -1), 10),
                ("RIGHTPADDING", (0, 0), (-1, -1), 10),
                ("TOPPADDING", (0, 1), (-1, -1), 7),
                ("BOTTOMPADDING", (0, 1), (-1, -1), 7),
            ]
        )
    )
    story.append(et)
    story.append(Spacer(1, 14))

    story.append(Image(str(imgs["results"]), width=6.8 * inch, height=2.0 * inch))
    story.append(P("Outcomes: visibility, automation, and scale", styles["caption"]))

    story.append(P("Implementation Approach", styles["h1"]))
    impl = [
        ("Discovery", "Mapped Rufheld's operational model and compliance requirements."),
        ("CRM Design", "Customized modules, fields, and workflows to match real-world case handling."),
        (
            "Automation",
            "Implemented Deluge scripts and Flow automation for case transitions, validation, and billing triggers.",
        ),
        ("Financial Alignment", "Connected successful case outcomes directly with revenue tracking."),
        ("Reporting Setup", "Configured dashboards for operational and financial insights."),
        ("System Hardening", "Added validation layers to ensure data integrity and process control."),
    ]
    for title, txt in impl:
        story.append(P(f"{title}", styles["h2"]))
        story.append(P(txt, styles["body"]))

    story.append(PageBreak())
    story.append(P("Results", styles["h1"]))
    for line in [
        "Manual work eliminated across key workflows",
        "Faster payment collection with automated billing",
        "Complete operational visibility in real time",
        "Improved scalability for higher case volumes",
        "End-to-end automation from lead to payment",
        "Accurate revenue tracking aligned with success-based model",
    ]:
        story.append(Paragraph(f"&bull; {escape(line)}", styles["bullet"]))

    story.append(Spacer(1, 14))
    story.append(P("Conclusion", styles["h1"]))
    story.append(
        P(
            "Rufheld transformed from a fragmented, manual operation into a fully automated, scalable system "
            "powered by Zoho and Stripe. The new system delivers strong alignment between service delivery and "
            "billing, improved cash flow, operational transparency, and scalable infrastructure for growth—while "
            "maintaining their core promise: pay only on success, backed by expert analysis and fast execution.",
            styles["body"],
        )
    )
    story.append(Spacer(1, 20))

    doc.build(
        story,
        onFirstPage=cover_canvas_factory(imgs["hero"], font, font_bold),
        onLaterPages=later_pages,
    )
    print("Saved:", out)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
