import { getWebsites } from "./actions"
import { WebsiteList } from "./components/website-list"
import { requirePermission } from "@/lib/auth"
import { Button } from "@/components/ui/button"
import { PlusCircle } from "lucide-react"
import Link from "next/link"

export const metadata = {
  title: "Quản lý Website - WooCenter",
  description: "Quản lý các website WooCommerce trong hệ thống",
}

export default async function WebsitesPage() {
  // Kiểm tra quyền truy cập
  await requirePermission("manage_websites")

  // Lấy danh sách website
  const websites = await getWebsites()

  return (
    <div className="container mx-auto py-6 space-y-6">
      <div className="flex justify-between items-center">
        <h1 className="text-3xl font-bold tracking-tight">Quản lý Website</h1>
        <Button asChild>
          <Link href="/websites/new">
            <PlusCircle className="mr-2 h-4 w-4" />
            Thêm Website
          </Link>
        </Button>
      </div>

      <WebsiteList websites={websites} />
    </div>
  )
}
