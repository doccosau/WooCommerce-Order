import { WebsiteForm } from "../components/website-form"
import { requirePermission } from "@/lib/auth"

export const metadata = {
  title: "Thêm Website - WooCenter",
  description: "Thêm website WooCommerce mới vào hệ thống",
}

export default async function NewWebsitePage() {
  // Kiểm tra quyền truy cập
  await requirePermission("manage_websites")

  return (
    <div className="container mx-auto py-6 space-y-6">
      <h1 className="text-3xl font-bold tracking-tight">Thêm Website Mới</h1>
      <WebsiteForm />
    </div>
  )
}
